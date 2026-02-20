<?php

namespace App\Http\Controllers;

use App\Models\CourierBalance;
use App\Models\CourierManifest;
use App\Models\CourierCurrentBalance;
use App\Models\FinancialLog;
use App\Models\Shipment;
use App\Services\Dashboard\CourierDashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CourierController extends Controller
{
    protected CourierDashboardService $dashboardService;

    public function __construct(CourierDashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }
    /**
     * Kurir dashboard
     */
    public function dashboard()
    {
        $data = $this->dashboardService->getDashboardData(Auth::user());

        return view('courier.dashboard', $data);
    }

    /**
     * Update shipment status
     */
    public function updateStatus(Request $request, Shipment $shipment)
    {
        // Verify ownership - can be origin courier or destination courier
        if ($shipment->courier_id !== Auth::id() && $shipment->destination_courier_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki akses ke paket ini.');
        }

        $validated = $request->validate([
            'status' => ['required', 'in:dalam_pengiriman,sampai_di_cabang_tujuan,diterima,cod_lunas'],
            'notes' => ['nullable', 'string', 'max:500'],
            'location' => ['nullable', 'string', 'max:255'],
        ]);

        // Prevent Linehaul courier from updating status if package has reached destination branch
        // Check if package has ever reached 'sampai_di_cabang_tujuan' status
        $hasReachedDestination = $shipment->status === 'sampai_di_cabang_tujuan' 
            || $shipment->statusHistories()
                ->where('status', 'sampai_di_cabang_tujuan')
                ->exists();
        
        if ($shipment->courier_id === Auth::id() && $hasReachedDestination) {
            return back()->withErrors(['status' => 'Kurir Linehaul tidak dapat mengubah status setelah paket sampai di cabang tujuan.']);
        }

        // Prevent Linehaul courier from updating to diterima
        if ($shipment->courier_id === Auth::id() && $validated['status'] === 'diterima') {
            return back()->withErrors(['status' => 'Kurir Linehaul tidak dapat mengubah status menjadi diterima.']);
        }
        
        // Prevent Linehaul courier from updating to sampai_di_cabang_tujuan if already reached
        if ($shipment->courier_id === Auth::id() && $validated['status'] === 'sampai_di_cabang_tujuan' && $hasReachedDestination) {
            return back()->withErrors(['status' => 'Kurir Linehaul tidak dapat mengubah status menjadi sampai di cabang tujuan karena paket sudah pernah sampai di cabang tujuan.']);
        }

        // Check if status transition is allowed
        if (!$shipment->canUpdateStatus($validated['status'])) {
            return back()->withErrors(['status' => 'Status tidak dapat diubah ke ' . $validated['status']]);
        }

        DB::transaction(function () use ($shipment, $validated) {
            // Update shipment based on status
            if ($validated['status'] === 'dalam_pengiriman') {
                $updateData = [
                    'status' => 'dalam_pengiriman',
                ];
                
                // Set out_for_delivery_at based on courier type
                if ($shipment->destination_courier_id === Auth::id()) {
                    // For destination courier, set destination_courier_out_for_delivery_at
                    $updateData['destination_courier_out_for_delivery_at'] = now();
                } else {
                    // For origin courier, set out_for_delivery_at
                    $updateData['out_for_delivery_at'] = now();
                }
                
                $shipment->update($updateData);
            } elseif ($validated['status'] === 'sampai_di_cabang_tujuan') {
                $shipment->update([
                    'status' => 'sampai_di_cabang_tujuan',
                ]);
            } elseif ($validated['status'] === 'diterima') {

                // Validate COD must be paid before status can be changed to diterima
                if ($shipment->type === 'cod' && $shipment->cod_status !== 'lunas') {
                    return back()->withErrors(['status' => 'Paket COD harus lunas terlebih dahulu sebelum status dapat diubah menjadi diterima.']);
                }
                

                $shipment->update([
                    'status' => 'diterima',
                    'delivered_at' => now(),
                    'delivery_notes' => $validated['notes'] ?? null,
                ]);
            } elseif ($validated['status'] === 'cod_lunas') {
                // Mark COD as paid
                $shipment->update([
                    'cod_status' => 'lunas',
                ]);

                // Record COD collection in balance (transaction log)
                CourierBalance::create([
                    'courier_id' => Auth::id(),
                    'shipment_id' => $shipment->id,
                    'type' => 'cod_collected',

                    'amount' => $shipment->total_cod_collectible,

                    'notes' => 'COD lunas - ' . $shipment->resi_number,
                ]);

                // Update current balance

                CourierCurrentBalance::updateBalance(Auth::id(), $shipment->total_cod_collectible, 'add');


                // Create financial log (immutable audit trail)
                FinancialLog::create([
                    'type' => 'COD_COLLECTED',
                    'reference_id' => $shipment->id,
                    'courier_id' => Auth::id(),

                    'amount' => $shipment->total_cod_collectible,

                    'actor_id' => Auth::id(),
                    'notes' => 'COD dikumpulkan untuk resi ' . $shipment->resi_number,
                    'metadata' => [
                        'resi_number' => $shipment->resi_number,
                        'payment_method' => $shipment->payment_method,
                    ],
                ]);
            } else {
                // Status: gagal
                $shipment->update([
                    'status' => 'gagal',
                    'delivery_notes' => $validated['notes'] ?? null,
                ]);
            }

            // Create status history
            $shipment->statusHistories()->create([
                'status' => $validated['status'],
                'updated_by' => Auth::id(),
                'notes' => $validated['notes'] ?? null,
                'location' => $validated['location'] ?? null,
            ]);

            // Update manifest counts
            if ($shipment->courier_id) {
                $manifest = CourierManifest::where('courier_id', $shipment->courier_id)
                    ->where('manifest_date', today())
                    ->where('status', 'active')
                    ->first();

                if ($manifest) {
                    $manifest->updateCounts();
                }
            }
        });

        return back()->with('success', 'Status paket berhasil diperbarui.');
    }

    /**
     * Get shipments by filter
     */
    public function getShipments(Request $request)
    {
        // Include both packages assigned as origin courier and destination courier
        // Disable BranchScope for courier to see all their assigned packages regardless of branch
        $query = Shipment::withoutGlobalScope(\App\Models\Scopes\BranchScope::class)
            ->where(function($q) {
                $q->where('courier_id', Auth::id())
                  ->orWhere('destination_courier_id', Auth::id());
            })
            ->whereIn('status', ['diproses', 'dalam_pengiriman', 'sampai_di_cabang_tujuan']);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $shipments = $query->with(['originBranch:id,name,code', 'destinationBranch:id,name,code'])
            ->latest()
            ->limit(100)
            ->get();

        return response()->json($shipments);
    }

    /**
     * Show COD dashboard for courier
     */
    public function codDashboard(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isKurir()) {
            abort(403);
        }

        // Filter by destination_courier_id (kurir tujuan yang mengantar dan menagih COD)
        // Include status sampai_di_cabang_tujuan (sudah di-assign tapi belum mulai mengantar) dan dalam_pengiriman (sudah mulai mengantar)
        $query = Shipment::where('destination_courier_id', $user->id)
            ->whereIn('status', ['sampai_di_cabang_tujuan', 'dalam_pengiriman'])
            ->with(['originBranch', 'destinationBranch']);

        // Filter by search
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('resi_number', 'like', '%' . $request->search . '%')
                  ->orWhere('receiver_name', 'like', '%' . $request->search . '%')
                  ->orWhere('receiver_phone', 'like', '%' . $request->search . '%');
            });
        }

        $codShipments = $query->latest('destination_courier_assigned_at')->paginate(20);

        // Calculate total COD to collect
        $totalCod = $codShipments->sum(function($shipment) {
            return $shipment->cod_amount + ($shipment->cod_shipping_cost ?? 0) + ($shipment->cod_admin_fee ?? 0);
        });

        return view('courier.cod-dashboard', compact('codShipments', 'totalCod'));
    }

    /**
     * Input COD payment
     */
    public function inputCodPayment(\App\Http\Requests\CodPaymentRequest $request, Shipment $shipment)
    {
        $user = Auth::user();
        
        if (!$user->isKurir()) {
            abort(403);
        }

        // Ensure only destination courier (Kurir Delivery) can input COD payment
        if ($shipment->destination_courier_id !== $user->id) {
            abort(403, 'Hanya Kurir Delivery yang dapat menagih COD.');
        }

        // Verify shipment is assigned to this courier
        if ($shipment->cod_collected_by !== $user->id) {
            abort(403, 'Paket ini tidak di-assign ke Anda.');
        }

        // Verify COD status
        if ($shipment->cod_status !== 'belum_lunas') {
            return back()->withErrors(['error' => 'Paket COD ini sudah lunas.']);
        }

        $validated = $request->validated();
        $expectedAmount = $shipment->cod_amount + ($shipment->cod_shipping_cost ?? 0) + ($shipment->cod_admin_fee ?? 0);

        // Verify amount matches
        if (abs($validated['amount'] - $expectedAmount) > 0.01) {
            return back()->withErrors(['error' => 'Jumlah pembayaran tidak sesuai. Jumlah yang harus ditagih: Rp ' . number_format($expectedAmount, 0, ',', '.')]);
        }

        try {
            DB::transaction(function () use ($shipment, $validated, $user) {
                $shipment->update([
                    'cod_status' => 'lunas',
                    'cod_payment_received_at' => now(),
                    'cod_collection_notes' => $validated['notes'] ?? null,
                    'status' => 'diterima',
                    'delivered_at' => now(),
                ]);

                // Create status history
                $shipment->statusHistories()->create([
                    'status' => 'diterima',
                    'updated_by' => $user->id,
                    'notes' => 'COD telah ditagih dan dibayar oleh kurir. ' . ($validated['notes'] ?? ''),
                ]);

                // Record COD collection in balance
                CourierBalance::create([
                    'courier_id' => $user->id,
                    'shipment_id' => $shipment->id,
                    'type' => 'cod_collected',
                    'amount' => $shipment->total_cod_collectible,
                    'notes' => 'COD lunas - ' . $shipment->resi_number,
                ]);

                // Update current balance
                CourierCurrentBalance::updateBalance($user->id, $shipment->total_cod_collectible, 'add');

                // Create financial log
                FinancialLog::create([
                    'type' => 'COD_COLLECTED',
                    'reference_id' => $shipment->id,
                    'courier_id' => $user->id,
                    'amount' => $shipment->total_cod_collectible,
                    'actor_id' => $user->id,
                    'notes' => 'COD dikumpulkan untuk resi ' . $shipment->resi_number,
                    'metadata' => [
                        'resi_number' => $shipment->resi_number,
                        'payment_method' => 'cash',
                    ],
                ]);
            });

            return redirect()->route('courier.cod.dashboard')
                ->with('success', 'Pembayaran COD berhasil dicatat. Status paket diubah menjadi Diterima.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Gagal mencatat pembayaran: ' . $e->getMessage()])->withInput();
        }
    }
}

