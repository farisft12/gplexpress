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
        // For PostgreSQL, we need to drop the default and constraint first, then alter
        DB::statement('ALTER TABLE shipments ALTER COLUMN cod_status DROP DEFAULT');
        
        // Drop the existing check constraint if it exists
        DB::statement("
            DO \$\$
            BEGIN
                IF EXISTS (
                    SELECT 1 FROM pg_constraint 
                    WHERE conname = 'shipments_cod_status_check' 
                    AND conrelid = (SELECT oid FROM pg_class WHERE relname = 'shipments')
                ) THEN
                    ALTER TABLE shipments DROP CONSTRAINT shipments_cod_status_check;
                END IF;
            END
            \$\$;
        ");

        // Make column nullable
        DB::statement('ALTER TABLE shipments ALTER COLUMN cod_status DROP NOT NULL');
        
        // Recreate the check constraint to allow null
        DB::statement("
            ALTER TABLE shipments 
            ADD CONSTRAINT shipments_cod_status_check 
            CHECK (cod_status IS NULL OR cod_status IN ('belum_lunas', 'lunas'))
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Set default value for null entries
        DB::statement("UPDATE shipments SET cod_status = 'belum_lunas' WHERE cod_status IS NULL");
        
        // Make column not null again
        DB::statement('ALTER TABLE shipments ALTER COLUMN cod_status SET NOT NULL');
        DB::statement("ALTER TABLE shipments ALTER COLUMN cod_status SET DEFAULT 'belum_lunas'");
        
        // Recreate original constraint
        DB::statement("
            ALTER TABLE shipments 
            ADD CONSTRAINT shipments_cod_status_check 
            CHECK (cod_status IN ('belum_lunas', 'lunas'))
        ");
    }
};







