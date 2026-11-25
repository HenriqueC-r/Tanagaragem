<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        // Preços em centavos. "tier" agrupa as variantes do mesmo plano.
        // Os slugs 'gratis' e 'loja-gratis' são usados pelo User::activePlan().
        $plans = [
            // ---- Conta física ----
            ['slug' => 'gratis',                  'tier' => 'free',    'name' => 'Free',                  'for_type' => 'person', 'price_cents' => 0,    'vehicle_limit' => 1,  'featured_limit' => 0],
            ['slug' => 'pessoa-plus',             'tier' => 'plus',    'name' => 'Plus',                  'for_type' => 'person', 'price_cents' => 990,  'vehicle_limit' => 5,  'featured_limit' => 0],
            ['slug' => 'pessoa-plus-destaque',    'tier' => 'plus',    'name' => 'Plus com destaques',    'for_type' => 'person', 'price_cents' => 1590, 'vehicle_limit' => 5,  'featured_limit' => 1],
            ['slug' => 'pessoa-premium',          'tier' => 'premium', 'name' => 'Premium',               'for_type' => 'person', 'price_cents' => 1490, 'vehicle_limit' => 10, 'featured_limit' => 0],
            ['slug' => 'pessoa-premium-destaque', 'tier' => 'premium', 'name' => 'Premium com destaques', 'for_type' => 'person', 'price_cents' => 2290, 'vehicle_limit' => 10, 'featured_limit' => 3],

            // ---- Conta loja ----
            ['slug' => 'loja-gratis',             'tier' => 'free',    'name' => 'Free',                  'for_type' => 'store',  'price_cents' => 0,    'vehicle_limit' => 5,  'featured_limit' => 0],
            ['slug' => 'loja-starter',            'tier' => 'starter', 'name' => 'Starter',               'for_type' => 'store',  'price_cents' => 3290, 'vehicle_limit' => 35, 'featured_limit' => 0],
            ['slug' => 'loja-starter-destaque',   'tier' => 'starter', 'name' => 'Starter com destaques', 'for_type' => 'store',  'price_cents' => 3990, 'vehicle_limit' => 35, 'featured_limit' => 3],
            ['slug' => 'loja-pro',                'tier' => 'pro',     'name' => 'Pro',                   'for_type' => 'store',  'price_cents' => 3990, 'vehicle_limit' => 50, 'featured_limit' => 0],
            ['slug' => 'loja-pro-destaque',       'tier' => 'pro',     'name' => 'Pro com destaques',     'for_type' => 'store',  'price_cents' => 4990, 'vehicle_limit' => 50, 'featured_limit' => 5],
            ['slug' => 'loja-premium',            'tier' => 'premium', 'name' => 'Premium',               'for_type' => 'store',  'price_cents' => 5290, 'vehicle_limit' => 80, 'featured_limit' => 0],
            ['slug' => 'loja-premium-destaque',   'tier' => 'premium', 'name' => 'Premium com destaques', 'for_type' => 'store',  'price_cents' => 6790, 'vehicle_limit' => 80, 'featured_limit' => 10],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(['slug' => $plan['slug']], $plan + ['is_active' => true]);
        }

        // Planos que não estão mais na tabela de preços: desativa, nunca apaga.
        // Quem já contratou continua com o plano; ele só deixa de ser vendido.
        Plan::whereNotIn('slug', array_column($plans, 'slug'))->update(['is_active' => false]);
    }
}
