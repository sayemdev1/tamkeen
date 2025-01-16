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
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade'); 
            $table->foreignId('store_id')->constrained()->onDelete('cascade'); 
            $table->string('shipment_type');          
            $table->string('starting_route');      
            $table->string('ending_route');           
            $table->date('arrived_date')->nullable(); 
            $table->enum('status', ['in_transit', 'delivered', 'cancelled']); 
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
        Schema::dropIfExists('shipments');
    }
};
