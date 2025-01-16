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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->string('name');
            $table->text('description');
            $table->decimal('price', 8, 2);
            $table->decimal('base_price', 8, 2); // Base price of the product
            $table->integer('stock')->default(0);
            $table->string('cover_image')->nullable();
            $table->string('background_image')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('products')->onDelete('cascade');
            $table->string('barcode')->nullable();
            $table->string('qr_code')->nullable();
            $table->string('serial_number')->nullable();
            $table->boolean('track_stock')->default(true);
            $table->string('track_stock_number')->nullable();
            $table->string('size')->nullable(); 
            $table->string('color')->nullable(); 
            $table->string('material')->nullable(); 
            $table->string('style')->nullable();
            $table->string('gender')->nullable();
            $table->string('capacity')->nullable();
            $table->string('weight')->nullable();
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
        Schema::dropIfExists('products');
    }
};
