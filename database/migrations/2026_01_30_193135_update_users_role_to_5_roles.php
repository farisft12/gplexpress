<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Map old roles to new roles
        // super_admin -> owner
        // manager_cabang -> manager
        // admin_cabang -> admin
        // courier_cabang -> kurir
        // admin (legacy) -> admin
        // manager (legacy) -> manager
        // kurir (legacy) -> kurir
        // user -> user (no change)

        DB::statement("
            UPDATE users 
            SET role = CASE 
                WHEN role = 'super_admin' THEN 'owner'
                WHEN role = 'manager_cabang' THEN 'manager'
                WHEN role = 'admin_cabang' THEN 'admin'
                WHEN role = 'courier_cabang' THEN 'kurir'
                ELSE role
            END
        ");

        // Drop existing check constraint
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

        // Add new constraint with 5 roles only
        DB::statement("
            ALTER TABLE users 
            ADD CONSTRAINT users_role_check 
            CHECK (role IN ('owner', 'manager', 'admin', 'kurir', 'user'))
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Map back to old roles (approximate)
        DB::statement("
            UPDATE users 
            SET role = CASE 
                WHEN role = 'owner' THEN 'super_admin'
                WHEN role = 'manager' THEN 'manager_cabang'
                WHEN role = 'admin' THEN 'admin_cabang'
                WHEN role = 'kurir' THEN 'courier_cabang'
                ELSE role
            END
        ");

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
            CHECK (role IN ('super_admin', 'manager_cabang', 'admin_cabang', 'courier_cabang', 'admin', 'manager', 'kurir', 'user'))
        ");
    }
};
