<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->decimal('balance', 15, 2)->default(0)->after('is_active');
        });

        Schema::table('vendors', function (Blueprint $table) {
            $table->decimal('balance', 15, 2)->default(0)->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('balance');
        });

        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn('balance');
        });
    }
};
