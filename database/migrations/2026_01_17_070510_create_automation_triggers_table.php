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
        Schema::create('automation_triggers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('automation_id')->constrained()->onDelete('cascade');
            $table->string('type'); // record_created, record_updated, date_reached, comment_added, status_changed
            $table->string('field_name')->nullable(); // field to monitor (e.g., end_date, status)
            $table->integer('offset_days')->nullable(); // for date_reached: -3, 0, +2 days
            $table->json('metadata')->nullable(); // additional trigger config
            $table->timestamps();
            
            $table->index(['automation_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('automation_triggers');
    }
};
