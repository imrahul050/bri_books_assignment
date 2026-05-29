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
        Schema::create('book', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('author');
            $table->string('cover_image')->nullable();
            $table->decimal('price', 10, 2)->default(0.00);
            $table->date('published_date')->date_format('Y-m-d');
            $table->tinyInteger('_deleted')->default(0);  
            $table->timestamps();

            // Indexes for search performance
            $table->index('title');
            $table->index('author');
            $table->index('published_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('book');
    }
};
