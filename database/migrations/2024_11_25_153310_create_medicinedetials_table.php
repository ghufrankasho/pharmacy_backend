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
        Schema::create('medicinedetials', function (Blueprint $table) {
            $table->id();
            $table->integer('quantity');
            $table->date('expire_date');
            $table->float('price');
            $table->string('component');
            $table->foreignId('medicine_id')->constrained('medicines','id')->default(null);
             $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicinedetials');
    }
};