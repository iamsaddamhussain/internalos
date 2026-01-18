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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->uuid('workspace_id');
            $table->foreignId('automation_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('record_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('type')->default('automation'); // automation, system, manual
            $table->string('title');
            $table->text('body');
            $table->json('metadata')->nullable(); // link, icon, color, etc.
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            
            $table->foreign('workspace_id')->references('id')->on('workspaces')->onDelete('cascade');
            $table->index(['user_id', 'read_at']);
            $table->index(['workspace_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
