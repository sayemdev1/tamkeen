<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateStoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Make 'type' and 'working_hours' nullable
        DB::statement("ALTER TABLE `stores` MODIFY `type` VARCHAR(255) NULL");
        DB::statement("ALTER TABLE `stores` MODIFY `working_hours` VARCHAR(255) NULL");

        // Add new columns
        DB::statement("ALTER TABLE `stores` ADD `store_email` VARCHAR(255) NULL");
        DB::statement("ALTER TABLE `stores` ADD `store_phone` VARCHAR(255) NULL");
        DB::statement("ALTER TABLE `stores` ADD `trn` VARCHAR(255) NULL");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revert the changes (if necessary)
        DB::statement("ALTER TABLE `stores` MODIFY `type` VARCHAR(255) NOT NULL"); // Make 'type' not nullable
        DB::statement("ALTER TABLE `stores` MODIFY `working_hours` VARCHAR(255) NOT NULL"); // Make 'working_hours' not nullable

        // Drop the newly added columns
        DB::statement("ALTER TABLE `stores` DROP COLUMN `store_email`");
        DB::statement("ALTER TABLE `stores` DROP COLUMN `store_phone`");
        DB::statement("ALTER TABLE `stores` DROP COLUMN `trn`");
    }
}
