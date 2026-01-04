<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('collections', function (Blueprint $table) {
            $table->boolean('enable_search')->default(true)->after('schema');
            $table->boolean('enable_export')->default(true)->after('enable_search');
            $table->integer('per_page')->default(10)->after('enable_export');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('collections', function (Blueprint $table) {
            $table->dropColumn(['enable_search', 'enable_export', 'per_page']);
        });
    }
};
