<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductVariantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id'); // Foreign Key
            $table->boolean('track_stock')->default(false);
            $table->string('stock_number')->nullable();
            $table->string('barcode')->nullable();
            $table->string('qr_code')->nullable();
            $table->string('serial_number')->nullable();
            $table->enum('size', ['XS', 'S', 'M', 'L', 'XL', 'XXL'])->nullable();
            $table->enum('gender', ['Male', 'Female', 'Unisex'])->nullable();
            $table->boolean('discount')->default(false);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('base_price', 10, 2)->nullable();
            $table->decimal('selling_price', 10, 2)->nullable();
            $table->string('material')->nullable();
            $table->decimal('weight', 10, 2)->nullable();
            $table->string('style')->nullable();
            $table->string('color')->nullable();
            $table->string('capacity')->nullable();
            $table->integer('stock')->nullable();
            $table->string('background_image_path')->nullable();
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_variants');
    }
}
