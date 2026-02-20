<?php

use App\Models\User;
use App\Models\Branch;
use App\Models\CourierSettlement;
use App\Models\CourierCurrentBalance;
use App\Models\CourierBalance;
use App\Models\Shipment;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->owner = User::factory()->create([
        'role' => 'owner',
        'status' => 'active',
    ]);

    $this->branch = Branch::create([
        'code' => Branch::generateCode(),
        'name' => 'Cabang Test',
        'city' => 'Jakarta',
        'address' => 'Jl. Test',
        'status' => 'active',
    ]);

    $this->courier = User::factory()->create([
        'role' => 'kurir',
        'status' => 'active',
        'branch_id' => $this->branch->id,
    ]);

    // Create COD shipment and mark as collected
    $this->shipment = Shipment::factory()->create([
        'branch_id' => $this->branch->id,
        'courier_id' => $this->courier->id,
        'type' => 'cod',
        'cod_amount' => 100000,
        'cod_status' => 'lunas',
    ]);

    // Create balance record
    CourierBalance::create([
        'courier_id' => $this->courier->id,
        'shipment_id' => $this->shipment->id,
        'type' => 'cod_collected',
        'amount' => 100000,
        'notes' => 'COD collected',
    ]);

    // Update current balance
    CourierCurrentBalance::updateBalance($this->courier->id, 100000, 'add');
});

test('owner can view settlement list', function () {
    CourierSettlement::factory()->create([
        'courier_id' => $this->courier->id,
        'branch_id' => $this->branch->id,
    ]);

    $response = $this->actingAs($this->owner)
        ->get('/admin/settlements');

    $response->assertStatus(200);
    $response->assertViewIs('admin.settlements.index');
});

test('owner can create settlement', function () {
    $response = $this->actingAs($this->owner)
        ->post('/admin/settlements', [
            'courier_id' => $this->courier->id,
            'amount' => 50000,
            'method' => 'cash',
            'notes' => 'Test settlement',
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('courier_settlements', [
        'courier_id' => $this->courier->id,
        'amount' => 50000,
        'method' => 'cash',
        'status' => 'pending',
    ]);
});

test('owner cannot create settlement exceeding balance', function () {
    $response = $this->actingAs($this->owner)
        ->post('/admin/settlements', [
            'courier_id' => $this->courier->id,
            'amount' => 200000, // Exceeds balance of 100000
            'method' => 'cash',
        ]);

    $response->assertSessionHasErrors('amount');
});

test('owner can confirm settlement', function () {
    $settlement = CourierSettlement::create([
        'branch_id' => $this->branch->id,
        'courier_id' => $this->courier->id,
        'amount' => 50000,
        'method' => 'cash',
        'status' => 'pending',
    ]);

    $response = $this->actingAs($this->owner)
        ->post("/admin/settlements/{$settlement->id}/confirm");

    $response->assertRedirect();
    $this->assertDatabaseHas('courier_settlements', [
        'id' => $settlement->id,
        'status' => 'confirmed',
        'confirmed_by' => $this->owner->id,
    ]);
});

test('settlement reduces courier balance', function () {
    $initialBalance = CourierCurrentBalance::getBalance($this->courier->id);

    $settlement = CourierSettlement::create([
        'branch_id' => $this->branch->id,
        'courier_id' => $this->courier->id,
        'amount' => 50000,
        'method' => 'cash',
        'status' => 'pending',
    ]);

    $this->actingAs($this->owner)
        ->post("/admin/settlements/{$settlement->id}/confirm");

    $newBalance = CourierCurrentBalance::getBalance($this->courier->id);
    expect($newBalance)->toBe($initialBalance - 50000);
});
