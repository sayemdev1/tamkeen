<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('membership_level_user', function (Blueprint $table) {
            DB::statement('ALTER TABLE `membership_level_user` CHANGE `is_subscibed` `is_subscribed` BOOLEAN');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('membership_level_user', function (Blueprint $table) {
            //
        });
    }
};
