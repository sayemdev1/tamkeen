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
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('coupon_type', ['discount', 'cashback', 'giftcard']);
            $table->string('promotion_code')->unique();
            $table->dateTime('expired_at');
            $table->enum('discount_type', ['percentage', 'fixed']);
            $table->decimal('percentage', 5, 2)->nullable(); // Only applicable for percentage type
            $table->enum('status', ['active', 'inactive', 'expired', 'used'])->default('active');
            $table->enum('visibility', ['public', 'private'])->default('public');
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
        Schema::dropIfExists('coupons');
    }
};
