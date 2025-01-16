<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateCouponsTableAddUsesAndUseForAndDropVisibility extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->integer('number_of_uses')->default(1)->after('status'); // Adding number of uses
            $table->enum('use_for', ['product', 'package', 'basket', 'order'])->after('number_of_uses'); // Adding use_for enum
            $table->dropColumn('visibility'); // Dropping visibility column
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->dropColumn('number_of_uses');
            $table->dropColumn('use_for');
            $table->enum('visibility', ['public', 'private'])->default('public'); // Adding visibility back
        });
    }
}
