<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->string('category')->default('car')->after('user_id');
            $table->boolean('single_owner')->default(false)->after('color');
            $table->index(['status', 'category']);
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropIndex(['status', 'category']);
            $table->dropColumn(['category', 'single_owner']);
        });
    }
};
