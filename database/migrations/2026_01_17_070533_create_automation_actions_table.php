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
        Schema::create('automation_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('automation_id')->constrained()->onDelete('cascade');
            $table->string('type'); // notify, email, update_field, create_record, webhook
            $table->string('target')->nullable(); // assignee, manager, creator, specific_user_id, role
            $table->string('channel')->nullable(); // in_app, email, both
            $table->json('config')->nullable(); // additional action configuration
            $table->integer('order')->default(0); // execution order
            $table->timestamps();
            
            $table->index(['automation_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('automation_actions');
    }
};
