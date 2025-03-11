<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('itineraire_destination', function (Blueprint $table) {
            $table->id();
            $table->foreignId('itineraire_id')->constrained('itineraires');
            $table->foreignId('destination_id')->constrained('destinations');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('itineraire_destination');
    }
};
