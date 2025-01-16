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
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referrer_id')->constrained('users'); // The user who is making the referral
            $table->foreignId('referee_id')->constrained('users');  // The user being referred
            $table->foreignId('package_id')->references('id')->on('membership_levels')->onDelete('cascade');
            $table->integer('level')->min(1)->max(4); // Level of the referral (1 to 4)
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
        Schema::dropIfExists('referrals');
    }
};
