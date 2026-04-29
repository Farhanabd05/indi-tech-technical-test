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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique()->index(); // TCK-2026-000001
            $table->string('title');
            $table->text('description');
            $table->string('status')->default('Open')->index(); // Menggunakan State Machine di Backend
            
            // Foreign Keys
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('priority_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete(); // Customer
            $table->foreignId('assigned_agent_id')->nullable()->constrained('users')->nullOnDelete(); // Agent
            
            // SLA & Time Tracking
            $table->timestamp('due_at')->nullable()->index(); // Batas waktu SLA
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
