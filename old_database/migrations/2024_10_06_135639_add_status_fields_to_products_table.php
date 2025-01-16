<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusFieldsToProductsTable extends Migration
{
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('added_to_wishlist')->default(false)->after('discounted_price'); // Indicates if the product is added to wishlist
            $table->boolean('added_to_cart')->default(false)->after('added_to_wishlist'); // Indicates if the product is added to cart
            $table->boolean('purchased')->default(false)->after('added_to_cart'); // Indicates if the product is purchased
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['added_to_wishlist', 'added_to_cart', 'purchased']);
        });
    }
}

