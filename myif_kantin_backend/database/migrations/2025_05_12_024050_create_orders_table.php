<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id('order_id');
            $table->unsignedBigInteger('user_id');
            $table->string('room_number', 50);
            $table->string('building_name', 100);
            $table->string('courier_name', 100)->nullable();
            $table->decimal('shipping_fee', 10, 2);
            $table->decimal('total_price', 10, 2);
            $table->string('status', 20);
            $table->string('payment_status', 20)->default('unpaid');
            $table->string('payment_method', 50)->nullable();
            $table->string('payment_proof')->nullable();
            $table->datetime('delivery_time')->nullable();
            $table->text('delivery_notes')->nullable();
            $table->timestamps();
            $table->foreign('user_id')->references('user_id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};