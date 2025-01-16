<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('membership_level_user', function (Blueprint $table) {
            $table->foreignId('referrer_id')->nullable()->constrained('users')->onDelete('set null')->after('membership_level_id');
        });        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('membership_level_user', function (Blueprint $table) {
            //
        });
    }
};
