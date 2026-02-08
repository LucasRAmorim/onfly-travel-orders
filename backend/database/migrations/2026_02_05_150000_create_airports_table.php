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
        Schema::create('airports', function (Blueprint $table) {
            $table->id();
            $table->string('iata_code', 3)->nullable();
            $table->string('icao_code', 4)->nullable();
            $table->string('name');
            $table->string('city');
            $table->string('country');
            $table->timestamps();

            $table->index('iata_code');
            $table->index('icao_code');
            $table->index('city');
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('airports');
    }
};
