<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePyramidReferralProfitsTable extends Migration
{
    public function up()
    {
        Schema::create('pyramid_referral_profits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referrer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('membership_level_id')->constrained('membership_levels')->onDelete('cascade');
            $table->decimal('profit', 10, 2);
            $table->unsignedTinyInteger('level'); // Referral level (1, 2, or 3)
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pyramid_referral_profits');
    }
}
