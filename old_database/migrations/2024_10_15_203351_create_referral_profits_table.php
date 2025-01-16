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
        Schema::create('referral_profits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referrer_id')->constrained('users'); // The user who is making the referral
            $table->foreignId('order_id')->constrained('orders');  // The user being referred
            $table->integer('profit');
            $table->integer('level')->nullable();
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
        Schema::dropIfExists('referral_profits');
    }
};
