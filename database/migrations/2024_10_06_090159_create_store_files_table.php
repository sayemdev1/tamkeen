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
        Schema::create('store_files', function (Blueprint $table) {
            $table->id(); // Auto-incrementing ID
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade'); // Foreign key referencing stores table
            $table->string('file_name')->nullable(); // Name of the file
            $table->string('file_path'); // Path to the stored file
            $table->string('file_type')->nullable(); // Type of the file (e.g., image, pdf)
            $table->timestamps(); // Created at and updated at timestamps
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('store_files');
    }
};
