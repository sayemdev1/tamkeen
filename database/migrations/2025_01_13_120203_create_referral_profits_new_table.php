<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReferralProfitsNewTable extends Migration
{
    public function up()
    {
        Schema::create('referral_profits_new', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referrer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('membership_level_id')->constrained('membership_levels')->onDelete('cascade');
            $table->decimal('profit', 10, 2);
            $table->unsignedTinyInteger('level'); // Level of referral
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('referral_profits_new');
    }
}
