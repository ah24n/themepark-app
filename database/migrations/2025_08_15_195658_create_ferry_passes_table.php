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
        Schema::create('ferry_passes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ferry_ticket_id')->constrained()->cascadeOnDelete();
            $table->dateTime('issued_at');
            $table->unsignedBigInteger('staff_id'); // could fk to users if staff are users
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ferry_passes');
    }
};
