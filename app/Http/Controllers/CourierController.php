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
        // Verify ownership
        if ($shipment->courier_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki akses ke paket ini.');
        }

        $validated = $request->validate([
            'status' => ['required', 'in:dalam_pengiriman,sampai_di_cabang_tujuan,diterima,cod_lunas'],
            'notes' => ['nullable', 'string', 'max:500'],
            'location' => ['nullable', 'string', 'max:255'],
        ]);

        // Check if status transition is allowed
        if (!$shipment->canUpdateStatus($validated['status'])) {
            return back()->withErrors(['status' => 'Status tidak dapat diubah ke ' . $validated['status']]);
        }

        DB::transaction(function () use ($shipment, $validated) {
            // Update shipment based on status
            if ($validated['status'] === 'dalam_pengiriman') {
                $shipment->update([
                    'status' => 'dalam_pengiriman',
                    'out_for_delivery_at' => now(),
                ]);
            } elseif ($validated['status'] === 'sampai_di_cabang_tujuan') {
                $shipment->update([
                    'status' => 'sampai_di_cabang_tujuan',
                ]);
            } elseif ($validated['status'] === 'diterima') {
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
                    'amount' => $shipment->cod_amount,
                    'notes' => 'COD lunas - ' . $shipment->resi_number,
                ]);

                // Update current balance
                CourierCurrentBalance::updateBalance(Auth::id(), $shipment->cod_amount, 'add');

                // Create financial log (immutable audit trail)
                FinancialLog::create([
                    'type' => 'COD_COLLECTED',
                    'reference_id' => $shipment->id,
                    'courier_id' => Auth::id(),
                    'amount' => $shipment->cod_amount,
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
        $query = Shipment::where('courier_id', Auth::id())
            ->whereIn('status', ['diproses', 'dalam_pengiriman']);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $shipments = $query->with(['originBranch:id,name,code', 'destinationBranch:id,name,code'])
            ->latest()
            ->limit(100)
            ->get();

        return response()->json($shipments);
    }
}

