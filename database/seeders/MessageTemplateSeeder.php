<?php

namespace Database\Seeders;

use App\Models\MessageTemplate;
use Illuminate\Database\Seeder;

class MessageTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            [
                'code' => 'paket_dikirim',
                'name' => 'Paket Dikirim',
                'description' => 'Notifikasi saat paket dikirim (status: diproses)',
                'channel' => 'whatsapp',
                'content' => "Halo {{receiver_name}},\n\nPaket Anda dengan nomor resi *{{resi}}* telah dikirim dari {{branch_name}}.\n\nStatus: {{status}}\n\nTerima kasih telah menggunakan layanan GPL Expres.",
                'branch_id' => null,
                'is_active' => true,
                'variables' => ['resi', 'status', 'receiver_name', 'branch_name'],
            ],
            [
                'code' => 'paket_dikirim',
                'name' => 'Paket Dikirim (Email)',
                'description' => 'Notifikasi email saat paket dikirim',
                'channel' => 'email',
                'content' => "Halo {{receiver_name}},\n\nPaket Anda dengan nomor resi {{resi}} telah dikirim dari {{branch_name}}.\n\nStatus: {{status}}\n\nTerima kasih telah menggunakan layanan GPL Expres.",
                'branch_id' => null,
                'is_active' => true,
                'variables' => ['resi', 'status', 'receiver_name', 'branch_name'],
            ],
            [
                'code' => 'kurir_otw',
                'name' => 'Kurir OTW',
                'description' => 'Notifikasi saat kurir sedang dalam perjalanan',
                'channel' => 'whatsapp',
                'content' => "Halo {{receiver_name}},\n\nKurir kami sedang dalam perjalanan untuk mengantarkan paket Anda.\n\nResi: *{{resi}}*\nKurir: {{courier_name}}\nETA: {{eta}}\n\nMohon siapkan alamat penerima. Terima kasih!",
                'branch_id' => null,
                'is_active' => true,
                'variables' => ['resi', 'receiver_name', 'courier_name', 'eta'],
            ],
            [
                'code' => 'paket_terkirim',
                'name' => 'Paket Terkirim',
                'description' => 'Notifikasi saat paket berhasil diterima',
                'channel' => 'whatsapp',
                'content' => "Halo {{receiver_name}},\n\nPaket Anda dengan nomor resi *{{resi}}* telah berhasil diterima.\n\nTerima kasih telah menggunakan layanan GPL Expres!",
                'branch_id' => null,
                'is_active' => true,
                'variables' => ['resi', 'receiver_name'],
            ],
            [
                'code' => 'cod_lunas',
                'name' => 'COD Lunas',
                'description' => 'Notifikasi saat pembayaran COD berhasil',
                'channel' => 'whatsapp',
                'content' => "Halo {{receiver_name}},\n\nPembayaran COD untuk paket *{{resi}}* telah berhasil diterima.\n\nJumlah: {{amount}}\nMetode: {{payment_method}}\n\nTerima kasih!",
                'branch_id' => null,
                'is_active' => true,
                'variables' => ['resi', 'receiver_name', 'amount', 'payment_method'],
            ],
            [
                'code' => 'gagal_antar',
                'name' => 'Gagal Antar',
                'description' => 'Notifikasi saat pengantaran gagal',
                'channel' => 'whatsapp',
                'content' => "Halo {{receiver_name}},\n\nMaaf, pengantaran paket *{{resi}}* mengalami kendala.\n\nKami akan mencoba mengantarkan kembali. Mohon hubungi customer service jika ada pertanyaan.\n\nTerima kasih.",
                'branch_id' => null,
                'is_active' => true,
                'variables' => ['resi', 'receiver_name'],
            ],
        ];

        foreach ($templates as $template) {
            // Use code + channel as unique identifier
            MessageTemplate::updateOrCreate(
                [
                    'code' => $template['code'],
                    'channel' => $template['channel'],
                    'branch_id' => $template['branch_id'],
                ],
                $template
            );
        }
    }
}
