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
            
            // Migrate data after dropping constraints
            DB::table('shipments')->where('status', 'menunggu')->update(['status' => 'pickup']);
            DB::table('shipments')->where('status', 'dibawa_kurir')->update(['status' => 'proses']);
            
            DB::table('shipment_status_histories')->where('status', 'menunggu')->update(['status' => 'pickup']);
            DB::table('shipment_status_histories')->where('status', 'dibawa_kurir')->update(['status' => 'proses']);
            
            // Add new check constraints with new status values
            DB::statement("
                ALTER TABLE shipments 
                ADD CONSTRAINT shipments_status_check 
                CHECK (status IN ('pickup', 'proses', 'dikirim', 'terkirim', 'gagal'))
            ");
            
            DB::statement("
                ALTER TABLE shipment_status_histories 
                ADD CONSTRAINT shipment_status_histories_status_check 
                CHECK (status IN ('pickup', 'proses', 'dikirim', 'terkirim', 'gagal', 'cod_belum_lunas', 'cod_lunas'))
            ");
            
            // Update default value
            DB::statement("ALTER TABLE shipments ALTER COLUMN status SET DEFAULT 'pickup'");
        } else {
            // MySQL/MariaDB
            DB::statement("ALTER TABLE shipments MODIFY COLUMN status ENUM('pickup', 'proses', 'dikirim', 'terkirim', 'gagal') DEFAULT 'pickup'");
            DB::statement("ALTER TABLE shipment_status_histories MODIFY COLUMN status ENUM('pickup', 'proses', 'dikirim', 'terkirim', 'gagal', 'cod_belum_lunas', 'cod_lunas')");
            
            // Migrate existing data
            DB::table('shipments')->where('status', 'menunggu')->update(['status' => 'pickup']);
            DB::table('shipments')->where('status', 'dibawa_kurir')->update(['status' => 'proses']);
            
            DB::table('shipment_status_histories')->where('status', 'menunggu')->update(['status' => 'pickup']);
            DB::table('shipment_status_histories')->where('status', 'dibawa_kurir')->update(['status' => 'proses']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Migrate data back
        DB::table('shipments')->where('status', 'pickup')->update(['status' => 'menunggu']);
        DB::table('shipments')->where('status', 'proses')->update(['status' => 'dibawa_kurir']);
        DB::table('shipments')->where('status', 'dikirim')->update(['status' => 'dibawa_kurir']);
        
        DB::table('shipment_status_histories')->where('status', 'pickup')->update(['status' => 'menunggu']);
        DB::table('shipment_status_histories')->where('status', 'proses')->update(['status' => 'dibawa_kurir']);
        DB::table('shipment_status_histories')->where('status', 'dikirim')->update(['status' => 'dibawa_kurir']);
        
        // Check database driver
        $driver = DB::connection()->getDriverName();
        
        if ($driver === 'pgsql') {
            // PostgreSQL: Revert enum type
            DB::statement("ALTER TYPE shipments_status_type RENAME TO shipments_status_type_new");
            DB::statement("CREATE TYPE shipments_status_type AS ENUM('menunggu', 'dibawa_kurir', 'terkirim', 'gagal')");
            DB::statement("ALTER TABLE shipments ALTER COLUMN status TYPE shipments_status_type USING status::text::shipments_status_type");
            DB::statement("ALTER TABLE shipments ALTER COLUMN status SET DEFAULT 'menunggu'");
            DB::statement("DROP TYPE shipments_status_type_new");
            
            DB::statement("ALTER TYPE shipment_status_histories_status_type RENAME TO shipment_status_histories_status_type_new");
            DB::statement("CREATE TYPE shipment_status_histories_status_type AS ENUM('menunggu', 'dibawa_kurir', 'terkirim', 'gagal', 'cod_belum_lunas', 'cod_lunas')");
            DB::statement("ALTER TABLE shipment_status_histories ALTER COLUMN status TYPE shipment_status_histories_status_type USING status::text::shipment_status_histories_status_type");
            DB::statement("DROP TYPE shipment_status_histories_status_type_new");
        } else {
            // MySQL/MariaDB
            DB::statement("ALTER TABLE shipments MODIFY COLUMN status ENUM('menunggu', 'dibawa_kurir', 'terkirim', 'gagal') DEFAULT 'menunggu'");
            DB::statement("ALTER TABLE shipment_status_histories MODIFY COLUMN status ENUM('menunggu', 'dibawa_kurir', 'terkirim', 'gagal', 'cod_belum_lunas', 'cod_lunas')");
        }
    }
};
