<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateUsersTable extends Migration
{
    public function up()
    {
        // Make 'gender' nullable and set default to 'male'
        DB::statement("ALTER TABLE `users` MODIFY `gender` ENUM('male', 'female') NULL DEFAULT 'male'");

        // Make 'phone' nullable
        DB::statement("ALTER TABLE `users` MODIFY `phone` VARCHAR(255) NULL");

        // Make 'date_of_birth' nullable
        DB::statement("ALTER TABLE `users` MODIFY `date_of_birth` DATE NULL");
    }

    public function down()
    {
        // Revert changes if needed
        DB::statement("ALTER TABLE `users` MODIFY `gender` ENUM('male', 'female') NOT NULL DEFAULT 'male'");
        DB::statement("ALTER TABLE `users` MODIFY `phone` VARCHAR(255) NOT NULL");
        DB::statement("ALTER TABLE `users` MODIFY `date_of_birth` DATE NOT NULL");
    }
}
