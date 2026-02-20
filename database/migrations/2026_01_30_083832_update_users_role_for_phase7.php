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
        // Drop existing check constraint if exists
        DB::statement("
            DO \$\$
            BEGIN
                IF EXISTS (
                    SELECT 1 FROM pg_constraint 
                    WHERE conname = 'users_role_check' 
                    AND conrelid = (SELECT oid FROM pg_class WHERE relname = 'users')
                ) THEN
                    ALTER TABLE users DROP CONSTRAINT users_role_check;
                END IF;
            END
            \$\$;
        ");

        // Add new constraint with Phase 7 roles
        DB::statement("
            ALTER TABLE users 
            ADD CONSTRAINT users_role_check 
            CHECK (role IN ('super_admin', 'manager_cabang', 'admin_cabang', 'courier_cabang', 'admin', 'manager', 'kurir', 'user'))
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the constraint
        DB::statement("
            DO \$\$
            BEGIN
                IF EXISTS (
                    SELECT 1 FROM pg_constraint 
                    WHERE conname = 'users_role_check' 
                    AND conrelid = (SELECT oid FROM pg_class WHERE relname = 'users')
                ) THEN
                    ALTER TABLE users DROP CONSTRAINT users_role_check;
                END IF;
            END
            \$\$;
        ");

        // Restore old constraint
        DB::statement("
            ALTER TABLE users 
            ADD CONSTRAINT users_role_check 
            CHECK (role IN ('admin', 'manager', 'kurir', 'user'))
        ");
    }
};
