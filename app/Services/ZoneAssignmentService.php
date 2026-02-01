<?php

namespace App\Services;

use App\Models\Zone;
use App\Models\Shipment;
use Illuminate\Support\Facades\Log;

class ZoneAssignmentService
{
    /**
     * Auto-assign zone to shipment based on receiver address
     */
    public function assignZone(Shipment $shipment): ?Zone
    {
        // Extract address components from receiver_address
        $address = $shipment->receiver_address;
        $city = $this->extractCity($address);
        $district = $this->extractDistrict($address);
        $postalCode = $this->extractPostalCode($address);

        // Find zone by address
        $zone = Zone::findByAddress($city, $district, $postalCode, $shipment->destination_branch_id);

        if ($zone) {
            $shipment->update(['zone_id' => $zone->id]);
            Log::info('Zone assigned to shipment', [
                'shipment_id' => $shipment->id,
                'zone_id' => $zone->id,
                'zone_name' => $zone->name,
            ]);
            return $zone;
        }

        // If no zone found, try to find default zone for branch
        $defaultZone = Zone::where('branch_id', $shipment->destination_branch_id)
            ->where('status', 'active')
            ->where('name', 'LIKE', '%default%')
            ->first();

        if ($defaultZone) {
            $shipment->update(['zone_id' => $defaultZone->id]);
            Log::info('Default zone assigned to shipment', [
                'shipment_id' => $shipment->id,
                'zone_id' => $defaultZone->id,
            ]);
            return $defaultZone;
        }

        Log::warning('No zone found for shipment', [
            'shipment_id' => $shipment->id,
            'address' => $address,
            'destination_branch_id' => $shipment->destination_branch_id,
        ]);

        return null;
    }

    /**
     * Extract city from address string
     */
    protected function extractCity(string $address): ?string
    {
        // Common city patterns in Indonesia
        $cities = ['Jakarta', 'Bandung', 'Surabaya', 'Yogyakarta', 'Semarang', 'Medan', 'Makassar', 'Palembang', 'Denpasar', 'Malang'];
        
        foreach ($cities as $city) {
            if (stripos($address, $city) !== false) {
                return $city;
            }
        }

        // Try to extract from common patterns like "Kota X" or "Kabupaten X"
        if (preg_match('/(?:Kota|Kabupaten)\s+([A-Za-z\s]+)/i', $address, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }

    /**
     * Extract district (kecamatan) from address string
     */
    protected function extractDistrict(string $address): ?string
    {
        // Try to extract from common patterns like "Kecamatan X" or "Kec. X"
        if (preg_match('/(?:Kecamatan|Kec\.?)\s+([A-Za-z\s]+)/i', $address, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }

    /**
     * Extract postal code from address string
     */
    protected function extractPostalCode(string $address): ?string
    {
        // Indonesian postal codes are 5 digits
        if (preg_match('/\b(\d{5})\b/', $address, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Manually assign zone (for admin override)
     */
    public function manualAssign(Shipment $shipment, int $zoneId, ?int $actorId = null): bool
    {
        $zone = Zone::find($zoneId);
        
        if (!$zone) {
            return false;
        }

        // Verify zone belongs to destination branch
        if ($zone->branch_id !== $shipment->destination_branch_id) {
            return false;
        }

        $shipment->update(['zone_id' => $zoneId]);

        Log::info('Zone manually assigned to shipment', [
            'shipment_id' => $shipment->id,
            'zone_id' => $zoneId,
            'actor_id' => $actorId ?? auth()->id(),
        ]);

        return true;
    }
}
