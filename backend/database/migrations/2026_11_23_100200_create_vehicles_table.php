<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('brand');
            $table->string('model');
            $table->string('version')->nullable();
            $table->unsignedSmallInteger('year_manufacture');
            $table->unsignedSmallInteger('year_model');
            $table->unsignedInteger('mileage')->default(0);
            $table->string('fuel');
            $table->string('transmission');
            $table->string('color')->nullable();
            $table->unsignedBigInteger('price_cents');
            $table->string('city');
            $table->string('state', 2);
            $table->text('description')->nullable();
            $table->string('status')->default('draft'); // draft, active, paused, sold
            $table->timestamp('featured_until')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['status', 'featured_until']);
            $table->index(['brand', 'model']);
            $table->index(['state', 'city']);
            $table->index('price_cents');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
