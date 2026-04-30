<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'requested_role')) {
                $table->string('requested_role', 20)->nullable()->after('role');
            }

            if (!Schema::hasColumn('users', 'dosen_request_status')) {
                $table->string('dosen_request_status', 20)->default('none')->after('requested_role');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'dosen_request_status')) {
                $table->dropColumn('dosen_request_status');
            }

            if (Schema::hasColumn('users', 'requested_role')) {
                $table->dropColumn('requested_role');
            }
        });
    }
};
