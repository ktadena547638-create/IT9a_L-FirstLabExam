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
        Schema::create('stock_items', function (Blueprint $table) {
            $table->id();
            $table->string('item_name', 255)->index();
            $table->string('sku', 50)->unique()->index();
            $table->string('category', 100)->index();
            $table->integer('quantity')->default(0)->index();
            $table->decimal('unit_price', 10, 2);
            $table->timestamps();
            
            // Performance optimization: Index frequently searched columns
            $table->index(['category', 'quantity']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_items');
    }
};
