<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_workspace', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->after('user_id')->constrained('roles')->onDelete('set null');
            $table->dropColumn('role');
        });
    }

    public function down(): void
    {
        Schema::table('user_workspace', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn('role_id');
            $table->string('role')->default('viewer')->after('user_id');
        });
    }
};
