<?php

use App\Models\User;
use App\Models\Shipment;
use App\Models\Branch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->owner = User::factory()->create([
        'role' => 'owner',
        'status' => 'active',
    ]);

    $this->admin = User::factory()->create([
        'role' => 'admin',
        'status' => 'active',
    ]);

    $this->branch1 = Branch::create([
        'code' => Branch::generateCode(),
        'name' => 'Cabang Jakarta',
        'city' => 'Jakarta',
        'address' => 'Jl. Test Jakarta',
        'status' => 'active',
    ]);

    $this->branch2 = Branch::create([
        'code' => Branch::generateCode(),
        'name' => 'Cabang Bandung',
        'city' => 'Bandung',
        'address' => 'Jl. Test Bandung',
        'status' => 'active',
    ]);
});

test('owner can create shipment', function () {
    $response = $this->actingAs($this->owner)->post('/admin/shipments', [
        'origin_branch_id' => $this->branch1->id,
        'destination_branch_id' => $this->branch2->id,
        'package_type' => 'Paket',
        'weight' => 1.5,
        'type' => 'non_cod',
        'shipping_cost' => 10000,
        'sender_name' => 'John Doe',
        'sender_phone' => '081234567890',
        'sender_address' => 'Jl. Sender',
        'receiver_name' => 'Jane Doe',
        'receiver_phone' => '081987654321',
        'receiver_address' => 'Jl. Receiver',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');
    $this->assertDatabaseHas('shipments', [
        'sender_name' => 'John Doe',
        'receiver_name' => 'Jane Doe',
    ]);
});

test('owner can create COD shipment', function () {
    $response = $this->actingAs($this->owner)->post('/admin/shipments', [
        'origin_branch_id' => $this->branch1->id,
        'destination_branch_id' => $this->branch2->id,
        'package_type' => 'Paket',
        'weight' => 1.5,
        'type' => 'cod',
        'cod_amount' => 50000,
        'sender_name' => 'John Doe',
        'sender_phone' => '081234567890',
        'sender_address' => 'Jl. Sender',
        'receiver_name' => 'Jane Doe',
        'receiver_phone' => '081987654321',
        'receiver_address' => 'Jl. Receiver',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('shipments', [
        'type' => 'cod',
        'cod_amount' => 50000,
        'cod_status' => 'belum_lunas',
    ]);
});

test('owner can view shipment list', function () {
    Shipment::factory()->count(5)->create([
        'branch_id' => $this->branch1->id,
    ]);

    $response = $this->actingAs($this->owner)->get('/admin/shipments');

    $response->assertStatus(200);
    $response->assertViewIs('admin.shipments.index');
});

test('owner can update shipment', function () {
    $shipment = Shipment::factory()->create([
        'branch_id' => $this->branch1->id,
        'status' => 'pickup',
        'sender_name' => 'Old Name',
    ]);

    $response = $this->actingAs($this->owner)->put("/admin/shipments/{$shipment->id}", [
        'origin_branch_id' => $this->branch1->id,
        'destination_branch_id' => $this->branch2->id,
        'package_type' => 'Paket',
        'weight' => 2.0,
        'type' => 'non_cod',
        'shipping_cost' => 15000,
        'sender_name' => 'New Name',
        'sender_phone' => '081234567890',
        'sender_address' => 'Jl. Sender',
        'receiver_name' => 'Jane Doe',
        'receiver_phone' => '081987654321',
        'receiver_address' => 'Jl. Receiver',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('shipments', [
        'id' => $shipment->id,
        'sender_name' => 'New Name',
    ]);
});

test('owner cannot update assigned shipment', function () {
    $shipment = Shipment::factory()->create([
        'branch_id' => $this->branch1->id,
        'status' => 'diproses',
    ]);

    $response = $this->actingAs($this->owner)->put("/admin/shipments/{$shipment->id}", [
        'origin_branch_id' => $this->branch1->id,
        'destination_branch_id' => $this->branch2->id,
        'package_type' => 'Paket',
        'weight' => 2.0,
        'type' => 'non_cod',
        'shipping_cost' => 15000,
        'sender_name' => 'New Name',
        'sender_phone' => '081234567890',
        'sender_address' => 'Jl. Sender',
        'receiver_name' => 'Jane Doe',
        'receiver_phone' => '081987654321',
        'receiver_address' => 'Jl. Receiver',
    ]);

    $response->assertSessionHasErrors('error');
});

test('owner can delete shipment', function () {
    $shipment = Shipment::factory()->create([
        'branch_id' => $this->branch1->id,
        'status' => 'pickup',
    ]);

    $response = $this->actingAs($this->owner)->delete("/admin/shipments/{$shipment->id}");

    $response->assertRedirect();
    $this->assertDatabaseMissing('shipments', [
        'id' => $shipment->id,
    ]);
});
