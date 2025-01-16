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
        Schema::table('products', function (Blueprint $table) {
            $table->enum('discount_type', ['percentage', 'fixed'])->nullable()->after('background_image'); // Type of discount
            $table->decimal('discount_value', 8, 2)->nullable()->after('discount_type'); // Value of the discount
            $table->date('start_date')->nullable()->after('discount_value'); // Start date of the discount
            $table->date('end_date')->nullable()->after('start_date'); // End date of the discount
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            //
        });
    }
};
