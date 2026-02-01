<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserPackageController extends Controller
{
    /**
     * Show user's package history
     */
    public function history()
    {
        $user = Auth::user();
        
        if (!$user->isUser()) {
            abort(403);
        }

        // Get packages where user is sender or receiver (by phone/name)
        // Optimized: prioritize exact phone match, then name match
        $packages = Shipment::where(function($query) use ($user) {
                if ($user->phone) {
                    // Exact phone match (indexed, fast)
                    $query->where('sender_phone', $user->phone)
                          ->orWhere('receiver_phone', $user->phone);
                }
                if ($user->name) {
                    // Name match (use LIKE only if phone not found)
                    // Note: For large datasets, consider adding full-text search index
                    $query->orWhere('sender_name', 'like', '%' . $user->name . '%')
                          ->orWhere('receiver_name', 'like', '%' . $user->name . '%');
                }
            })
            ->with(['originBranch', 'destinationBranch', 'courier'])
            ->latest()
            ->paginate(20);

        return view('user.package-history', compact('packages'));
    }

    /**
     * Show package detail for review
     */
    public function show(Shipment $shipment)
    {
        $user = Auth::user();
        
        if (!$user->isUser()) {
            abort(403);
        }

        // Verify user owns this package
        $isOwner = ($user->phone && ($shipment->sender_phone === $user->phone || $shipment->receiver_phone === $user->phone))
                || ($user->name && (stripos($shipment->sender_name, $user->name) !== false || stripos($shipment->receiver_name, $user->name) !== false));

        if (!$isOwner) {
            abort(403, 'Anda tidak memiliki akses ke paket ini.');
        }

        // Load existing review if any
        $review = Review::where('shipment_id', $shipment->id)
            ->where('user_id', $user->id)
            ->first();

        return view('user.package-detail', compact('shipment', 'review'));
    }

    /**
     * Submit review for delivered package
     */
    public function review(Request $request, Shipment $shipment)
    {
        $user = Auth::user();
        
        if (!$user->isUser()) {
            abort(403);
        }

        // Verify user owns this package and it's delivered
        $isOwner = ($user->phone && ($shipment->sender_phone === $user->phone || $shipment->receiver_phone === $user->phone))
                || ($user->name && (stripos($shipment->sender_name, $user->name) !== false || stripos($shipment->receiver_name, $user->name) !== false));

        if (!$isOwner || $shipment->status !== 'diterima') {
            abort(403);
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:500'],
        ]);

        // Check if review already exists
        $existingReview = Review::where('shipment_id', $shipment->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existingReview) {
            return back()->withErrors(['rating' => 'Anda sudah memberikan review untuk paket ini.']);
        }

        // Create review
        Review::create([
            'shipment_id' => $shipment->id,
            'user_id' => $user->id,
            'reviewer_name' => $user->name,
            'reviewer_email' => $user->email,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
        ]);

        return back()->with('success', 'Review berhasil dikirim. Terima kasih!');
    }
}
