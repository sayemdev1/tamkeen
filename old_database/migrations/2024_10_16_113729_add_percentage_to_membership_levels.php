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
        Schema::table('membership_levels', function (Blueprint $table) {
            $table->integer('percentage_in_level_1')->default(0);
            $table->integer('percentage_in_level_2')->default(0);
            $table->integer('percentage_in_level_3')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('membership_levels', function (Blueprint $table) {
            //
        });
    }
};
