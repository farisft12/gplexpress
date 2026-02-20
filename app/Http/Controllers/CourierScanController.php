<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CourierScanController extends Controller
{
    /**
     * Show scan resi page
     */
    public function scanForm()
    {
        $user = Auth::user();
        
        if (!$user->isKurir()) {
            abort(403);
        }

        return view('courier.scan');
    }

    /**
     * Process scanned resi - Ambil Paket
     */
    public function scanResi(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isKurir()) {
            abort(403);
        }

        $validated = $request->validate([
            'resi_number' => ['required', 'string'],
        ]);

        // Remove global scope to allow scanning unassigned packages from courier's branch
        $shipment = Shipment::withoutGlobalScope(\App\Models\Scopes\BranchScope::class)
            ->where('resi_number', trim($validated['resi_number']))
            ->first();

        if (!$shipment) {
            return back()->withErrors(['resi_number' => 'Nomor resi tidak ditemukan.']);
        }

        // Check if shipment belongs to courier's branch
        if ($user->branch_id && $shipment->branch_id !== $user->branch_id) {
            return back()->withErrors(['resi_number' => 'Paket ini bukan dari cabang Anda.']);
        }

        // Check if shipment is already assigned to another courier
        if ($shipment->courier_id && $shipment->courier_id !== $user->id) {
            return back()->withErrors(['resi_number' => 'Paket ini sudah diambil oleh kurir lain.']);
        }

        // Check if shipment status allows pickup (should be 'pickup')
        if ($shipment->status !== 'pickup') {
            return back()->withErrors(['resi_number' => 'Paket ini tidak dapat diambil. Status: ' . ucfirst(str_replace('_', ' ', $shipment->status))]);
        }

        // Update shipment: assign to courier and mark as "diproses"
        DB::transaction(function () use ($shipment, $user) {
            $shipment->update([
                'courier_id' => $user->id,
                'status' => 'diproses',
                'assigned_at' => now(),
            ]);

            // Create status history
            $shipment->statusHistories()->create([
                'status' => 'diproses',
                'updated_by' => $user->id,
                'notes' => 'Paket diambil oleh kurir: ' . $user->name . ' (Scan Resi)',
            ]);
        });

        return redirect()->route('courier.scan')->with('success', 'Paket berhasil diambil: ' . $shipment->resi_number);
    }

    /**
     * List packages taken by courier
     */
    public function myPackages()
    {
        $user = Auth::user();
        
        if (!$user->isKurir()) {
            abort(403);
        }

        // Optimize: eager load with specific columns to reduce memory usage
        $packages = Shipment::where('courier_id', $user->id)
            ->with([
                'originBranch:id,name,code',
                'destinationBranch:id,name,code',
                'zone:id,name'
            ])
            ->latest()
            ->paginate(20);

        return view('courier.my-packages', compact('packages'));
    }

    /**
     * Show package detail for courier
     */
    public function show(Shipment $shipment)
    {
        $user = Auth::user();
        
        if (!$user->isKurir()) {
            abort(403);
        }

        // Verify that this shipment belongs to the courier
        if ($shipment->courier_id !== $user->id) {
            abort(403, 'Anda tidak memiliki akses ke paket ini.');
        }

        $shipment->load(['courier', 'statusHistories.updater', 'originBranch', 'destinationBranch']);
        return view('courier.package-detail', compact('shipment'));
    }

    /**
     * Bulk update status for multiple packages
     */
    public function bulkUpdateStatus(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isKurir()) {
            abort(403);
        }

        $validated = $request->validate([
            'package_ids' => ['required', 'array', 'min:1'],
            'package_ids.*' => ['exists:shipments,id'],
            'status' => ['required', 'in:dalam_pengiriman,sampai_di_cabang_tujuan,diterima'],
        ]);

        $packages = Shipment::whereIn('id', $validated['package_ids'])
            ->where('courier_id', $user->id)
            ->get();

        $updated = 0;
        $failed = 0;

        DB::transaction(function () use ($packages, $validated, $user, &$updated, &$failed) {
            foreach ($packages as $package) {
                // Check if status transition is allowed
                if (!$package->canUpdateStatus($validated['status'])) {
                    $failed++;
                    continue;
                }

                // Update status
                if ($validated['status'] === 'dalam_pengiriman') {
                    $package->update([
                        'status' => 'dalam_pengiriman',
                        'out_for_delivery_at' => now(),
                    ]);
                } elseif ($validated['status'] === 'sampai_di_cabang_tujuan') {
                    $package->update([
                        'status' => 'sampai_di_cabang_tujuan',
                    ]);
                } elseif ($validated['status'] === 'diterima') {
<<<<<<< HEAD
                    // Validate COD must be paid before status can be changed to diterima
                    if ($package->type === 'cod' && $package->cod_status !== 'lunas') {
                        $failed++;
                        continue; // Skip this package
                    }
                    
=======
>>>>>>> 8415c2504e0943d7af6fcb75f06c3f500ecde573
                    $package->update([
                        'status' => 'diterima',
                        'delivered_at' => now(),
                    ]);
                }

                // Create status history
                $package->statusHistories()->create([
                    'status' => $validated['status'],
                    'updated_by' => $user->id,
                    'notes' => 'Bulk update status',
                ]);

                $updated++;
            }
        });

        $message = "Berhasil mengupdate {$updated} paket";
        if ($failed > 0) {
            $message .= ", {$failed} paket gagal diupdate";
        }

        return redirect()->route('courier.my-packages')->with('success', $message);
    }
}
