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
        Schema::table('packages', function (Blueprint $table) {
            $table->integer('profit_percentage_in_level_1')->default(0)->after('total_price');
            $table->integer('profit_percentage_in_level_2')->default(0)->after('profit_percentage_in_level_1');
            $table->integer('profit_percentage_in_level_3')->default(0)->after('profit_percentage_in_level_2');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('packages', function (Blueprint $table) {
            //
        });
    }
};
