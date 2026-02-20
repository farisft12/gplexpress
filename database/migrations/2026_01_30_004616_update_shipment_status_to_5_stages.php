<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check database driver
        $driver = DB::connection()->getDriverName();
        
        if ($driver === 'pgsql') {
            // PostgreSQL: Drop check constraints first
            DB::statement("
                DO \$\$
                BEGIN
                    IF EXISTS (
                        SELECT 1 FROM pg_constraint 
                        WHERE conname = 'shipments_status_check' 
                        AND conrelid = (SELECT oid FROM pg_class WHERE relname = 'shipments')
                    ) THEN
                        ALTER TABLE shipments DROP CONSTRAINT shipments_status_check;
                    END IF;
                END
                \$\$;
            ");
            
            DB::statement("
                DO \$\$
                BEGIN
                    IF EXISTS (
                        SELECT 1 FROM pg_constraint 
                        WHERE conname = 'shipment_status_histories_status_check' 
                        AND conrelid = (SELECT oid FROM pg_class WHERE relname = 'shipment_status_histories')
                    ) THEN
                        ALTER TABLE shipment_status_histories DROP CONSTRAINT shipment_status_histories_status_check;
                    END IF;
                END
                \$\$;
            ");
            
            // Migrate existing data
            DB::table('shipments')->where('status', 'proses')->update(['status' => 'diproses']);
            DB::table('shipments')->where('status', 'dikirim')->update(['status' => 'dalam_pengiriman']);
            DB::table('shipments')->where('status', 'terkirim')->update(['status' => 'sampai_di_cabang_tujuan']);
            // Gagal status - bisa diubah ke status terakhir yang relevan atau dibiarkan untuk diupdate manual
            DB::table('shipments')->where('status', 'gagal')->update(['status' => 'dalam_pengiriman']);
            
            DB::table('shipment_status_histories')->where('status', 'proses')->update(['status' => 'diproses']);
            DB::table('shipment_status_histories')->where('status', 'dikirim')->update(['status' => 'dalam_pengiriman']);
            DB::table('shipment_status_histories')->where('status', 'terkirim')->update(['status' => 'sampai_di_cabang_tujuan']);
            DB::table('shipment_status_histories')->where('status', 'gagal')->update(['status' => 'dalam_pengiriman']);
            
            // Add new check constraints with new status values
            DB::statement("
                ALTER TABLE shipments 
                ADD CONSTRAINT shipments_status_check 
                CHECK (status IN ('pickup', 'diproses', 'dalam_pengiriman', 'sampai_di_cabang_tujuan', 'diterima'))
            ");
            
            DB::statement("
                ALTER TABLE shipment_status_histories 
                ADD CONSTRAINT shipment_status_histories_status_check 
                CHECK (status IN ('pickup', 'diproses', 'dalam_pengiriman', 'sampai_di_cabang_tujuan', 'diterima', 'cod_belum_lunas', 'cod_lunas'))
            ");
            
            // Update default value
            DB::statement("ALTER TABLE shipments ALTER COLUMN status SET DEFAULT 'pickup'");
        } else {
            // MySQL/MariaDB
            DB::statement("ALTER TABLE shipments MODIFY COLUMN status ENUM('pickup', 'diproses', 'dalam_pengiriman', 'sampai_di_cabang_tujuan', 'diterima') DEFAULT 'pickup'");
            DB::statement("ALTER TABLE shipment_status_histories MODIFY COLUMN status ENUM('pickup', 'diproses', 'dalam_pengiriman', 'sampai_di_cabang_tujuan', 'diterima', 'cod_belum_lunas', 'cod_lunas')");
            
            // Migrate existing data
            DB::table('shipments')->where('status', 'proses')->update(['status' => 'diproses']);
            DB::table('shipments')->where('status', 'dikirim')->update(['status' => 'dalam_pengiriman']);
            DB::table('shipments')->where('status', 'terkirim')->update(['status' => 'sampai_di_cabang_tujuan']);
            DB::table('shipments')->where('status', 'gagal')->update(['status' => 'dalam_pengiriman']);
            
            DB::table('shipment_status_histories')->where('status', 'proses')->update(['status' => 'diproses']);
            DB::table('shipment_status_histories')->where('status', 'dikirim')->update(['status' => 'dalam_pengiriman']);
            DB::table('shipment_status_histories')->where('status', 'terkirim')->update(['status' => 'sampai_di_cabang_tujuan']);
            DB::table('shipment_status_histories')->where('status', 'gagal')->update(['status' => 'dalam_pengiriman']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Check database driver
        $driver = DB::connection()->getDriverName();
        
        // Migrate data back
        DB::table('shipments')->where('status', 'diproses')->update(['status' => 'proses']);
        DB::table('shipments')->where('status', 'dalam_pengiriman')->update(['status' => 'dikirim']);
        DB::table('shipments')->where('status', 'sampai_di_cabang_tujuan')->update(['status' => 'terkirim']);
        DB::table('shipments')->where('status', 'diterima')->update(['status' => 'terkirim']);
        
        DB::table('shipment_status_histories')->where('status', 'diproses')->update(['status' => 'proses']);
        DB::table('shipment_status_histories')->where('status', 'dalam_pengiriman')->update(['status' => 'dikirim']);
        DB::table('shipment_status_histories')->where('status', 'sampai_di_cabang_tujuan')->update(['status' => 'terkirim']);
        DB::table('shipment_status_histories')->where('status', 'diterima')->update(['status' => 'terkirim']);
        
        if ($driver === 'pgsql') {
            // Revert constraints
            DB::statement("
                DO \$\$
                BEGIN
                    IF EXISTS (
                        SELECT 1 FROM pg_constraint 
                        WHERE conname = 'shipments_status_check' 
                        AND conrelid = (SELECT oid FROM pg_class WHERE relname = 'shipments')
                    ) THEN
                        ALTER TABLE shipments DROP CONSTRAINT shipments_status_check;
                    END IF;
                END
                \$\$;
            ");
            
            DB::statement("
                ALTER TABLE shipments 
                ADD CONSTRAINT shipments_status_check 
                CHECK (status IN ('pickup', 'proses', 'dikirim', 'terkirim', 'gagal'))
            ");
            
            DB::statement("
                DO \$\$
                BEGIN
                    IF EXISTS (
                        SELECT 1 FROM pg_constraint 
                        WHERE conname = 'shipment_status_histories_status_check' 
                        AND conrelid = (SELECT oid FROM pg_class WHERE relname = 'shipment_status_histories')
                    ) THEN
                        ALTER TABLE shipment_status_histories DROP CONSTRAINT shipment_status_histories_status_check;
                    END IF;
                END
                \$\$;
            ");
            
            DB::statement("
                ALTER TABLE shipment_status_histories 
                ADD CONSTRAINT shipment_status_histories_status_check 
                CHECK (status IN ('pickup', 'proses', 'dikirim', 'terkirim', 'gagal', 'cod_belum_lunas', 'cod_lunas'))
            ");
            
            DB::statement("ALTER TABLE shipments ALTER COLUMN status SET DEFAULT 'pickup'");
        } else {
            // MySQL/MariaDB
            DB::statement("ALTER TABLE shipments MODIFY COLUMN status ENUM('pickup', 'proses', 'dikirim', 'terkirim', 'gagal') DEFAULT 'pickup'");
            DB::statement("ALTER TABLE shipment_status_histories MODIFY COLUMN status ENUM('pickup', 'proses', 'dikirim', 'terkirim', 'gagal', 'cod_belum_lunas', 'cod_lunas')");
        }
    }
};
