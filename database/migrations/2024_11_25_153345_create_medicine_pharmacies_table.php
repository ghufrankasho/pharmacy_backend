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
        Schema::create('medicine_pharmacies', function (Blueprint $table) {
            $table->id();
            $table->string('confirmed')->default('wait');//done , wait, deny
            $table->integer('quantity');
            $table->foreignId('medicine_id')->constrained('medicines','id')->default(null);
            $table->foreignId('pharmacy_id')->constrained('pharmacies','id')->default(null);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicine_pharmacies');
    }
};