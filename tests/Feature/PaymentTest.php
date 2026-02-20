<?php

use App\Models\User;
use App\Models\Shipment;
use App\Models\Branch;
use App\Models\PaymentTransaction;
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

    $this->codShipment = Shipment::factory()->create([
        'branch_id' => $this->branch->id,
        'type' => 'cod',
        'cod_amount' => 50000,
        'cod_status' => 'belum_lunas',
        'status' => 'sampai_di_cabang_tujuan',
    ]);
});

test('owner can view payment form for COD shipment', function () {
    $response = $this->actingAs($this->owner)
        ->get("/admin/payments/{$this->codShipment->id}/form");

    $response->assertStatus(200);
    $response->assertViewIs('admin.shipments.payment');
});

test('owner cannot view payment form for non-COD shipment', function () {
    $nonCodShipment = Shipment::factory()->create([
        'branch_id' => $this->branch->id,
        'type' => 'non_cod',
        'status' => 'sampai_di_cabang_tujuan',
    ]);

    $response = $this->actingAs($this->owner)
        ->get("/admin/payments/{$nonCodShipment->id}/form");

    $response->assertSessionHasErrors('error');
});

test('owner can process cash payment', function () {
    $response = $this->actingAs($this->owner)
        ->post("/admin/payments/{$this->codShipment->id}/cash");

    $response->assertRedirect();
    $this->assertDatabaseHas('shipments', [
        'id' => $this->codShipment->id,
        'payment_method' => 'cash',
        'cod_status' => 'lunas',
        'payment_status' => 'settlement',
    ]);
});

test('owner cannot process payment for already paid shipment', function () {
    $this->codShipment->update([
        'cod_status' => 'lunas',
        'payment_method' => 'cash',
    ]);

    $response = $this->actingAs($this->owner)
        ->post("/admin/payments/{$this->codShipment->id}/cash");

    $response->assertSessionHasErrors('error');
});

test('owner can view payment list', function () {
    PaymentTransaction::factory()->count(3)->create();

    $response = $this->actingAs($this->owner)
        ->get('/admin/payments');

    $response->assertStatus(200);
    $response->assertViewIs('admin.payments.index');
});

test('owner can view failed payments', function () {
    PaymentTransaction::factory()->create([
        'status' => 'expire',
    ]);

    $response = $this->actingAs($this->owner)
        ->get('/admin/payments/failed');

    $response->assertStatus(200);
    $response->assertViewIs('admin.payments.failed');
});
