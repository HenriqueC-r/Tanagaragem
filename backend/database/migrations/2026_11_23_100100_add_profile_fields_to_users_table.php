<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('type')->default('person')->after('password'); // person ou store
            $table->string('cpf', 11)->unique()->after('type');
            $table->string('phone', 20)->nullable()->after('cpf');
            $table->string('city')->nullable()->after('phone');
            $table->string('state', 2)->nullable()->after('city');
            $table->string('company_name')->nullable()->after('state');
            $table->string('cnpj', 14)->nullable()->unique()->after('company_name');
            $table->foreignId('plan_id')->nullable()->after('cnpj')
                ->constrained('plans')->nullOnDelete();
            $table->timestamp('plan_expires_at')->nullable()->after('plan_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('plan_id');
            $table->dropUnique(['cpf']);
            $table->dropUnique(['cnpj']);
            $table->dropColumn([
                'type', 'cpf', 'phone', 'city', 'state',
                'company_name', 'cnpj', 'plan_expires_at',
            ]);
        });
    }
};
