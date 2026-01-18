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
        Schema::create('automation_conditions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('automation_id')->constrained()->onDelete('cascade');
            $table->string('field'); // field to check
            $table->string('operator'); // =, !=, >, <, >=, <=, contains, not_contains
            $table->text('value'); // value to compare against
            $table->string('condition_group')->default('default'); // for AND/OR grouping
            $table->timestamps();
            
            $table->index(['automation_id', 'condition_group']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('automation_conditions');
    }
};
