<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
   {
       Schema::create('menus', function (Blueprint $table) {
           $table->id('menu_id');
           $table->unsignedBigInteger('vendor_id');
           $table->string('name', 100);
           $table->text('description');
           $table->string('photo_url')->nullable();
           $table->decimal('price', 10, 2);
           $table->boolean('is_available')->default(true);
           $table->timestamps();
           $table->foreign('vendor_id')->references('vendor_id')->on('vendors');
       });
   }

   public function down(): void
   {
       Schema::dropIfExists('menus');
   }
};