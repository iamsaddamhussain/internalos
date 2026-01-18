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
        Schema::create('automation_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('automation_id')->constrained()->onDelete('cascade');
            $table->foreignId('record_id')->nullable()->constrained()->onDelete('set null');
            $table->string('status'); // success, failed, skipped
            $table->text('message')->nullable();
            $table->json('context')->nullable(); // trigger data, condition results, etc.
            $table->timestamp('executed_at');
            $table->timestamps();
            
            $table->index(['automation_id', 'executed_at']);
            $table->index(['status', 'executed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('automation_logs');
    }
};
