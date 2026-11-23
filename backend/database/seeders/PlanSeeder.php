<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        // Valores de EXEMPLO: ajuste preços e limites quando decidir.
        $plans = [
            ['slug' => 'gratis',    'name' => 'Grátis',     'for_type' => 'person', 'price_cents' => 0,     'vehicle_limit' => 1],
            ['slug' => 'pessoa-3',  'name' => 'Pessoa 3',   'for_type' => 'person', 'price_cents' => 2990,  'vehicle_limit' => 3],
            ['slug' => 'pessoa-5',  'name' => 'Pessoa 5',   'for_type' => 'person', 'price_cents' => 4990,  'vehicle_limit' => 5],
            ['slug' => 'loja-10',   'name' => 'Loja 10',    'for_type' => 'store',  'price_cents' => 9900,  'vehicle_limit' => 10],
            ['slug' => 'loja-30',   'name' => 'Loja 30',    'for_type' => 'store',  'price_cents' => 19900, 'vehicle_limit' => 30],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(['slug' => $plan['slug']], $plan);
        }
    }
}
