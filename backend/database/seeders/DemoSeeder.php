<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local')) {
            $this->command?->error('DemoSeeder só roda em ambiente local.');

            return;
        }

        $this->cleanup();

        $storePlan = Plan::where('slug', 'loja-premium')->first();

        $stores = [
            's1' => ['Auto Dourados', 'Dourados'],
            's2' => ['Campo Grande Veiculos', 'Campo Grande'],
            's3' => ['Bonito Motors', 'Bonito'],
        ];

        $storeUsers = [];
        $n = 0;

        foreach ($stores as $key => [$company, $city]) {
            $n++;
            $user = User::create([
                'name' => "Dono {$company}",
                'email' => "loja{$n}@demo.tanagaragem.test",
                'password' => 'demo12345',
                'type' => User::TYPE_STORE,
                'cpf' => '9' . str_pad((string) $n, 10, '0', STR_PAD_LEFT),
                'phone' => '67999990000',
                'city' => $city,
                'state' => 'MS',
                'company_name' => $company,
                'cnpj' => '9' . str_pad((string) $n, 13, '0', STR_PAD_LEFT),
            ]);

            // plano e aprovação ficam fora do fillable: gravamos com forceFill
            $user->forceFill([
                'cnpj_verified_at' => now(),
                'plan_id' => $storePlan?->id,
            ])->save();

            $storeUsers[$key] = $user;
        }

        // vendedor, categoria, marca, modelo, versão, ano, km, combustível,
        // câmbio, preço (centavos), cidade, único dono, destaque
        $rows = [
            ['p',  'car',        'Chevrolet',     'Onix',    'LTZ 1.0',    2022, 31000,  'flex',   'manual',    7490000,  'Bonito',       false, true],
            ['p',  'car',        'Volkswagen',    'Gol',     'Trend',      2019, 68000,  'flex',   'manual',    5290000,  'Campo Grande', true,  true],
            ['s1', 'car',        'Toyota',        'Corolla', 'XEi',        2021, 42000,  'flex',   'automatic', 10990000, 'Dourados',     false, false],
            ['p',  'motorcycle', 'Honda',         'CG 160',  'Start',      2023, 9000,   'flex',   'manual',    1750000,  'Bonito',       false, false],
            ['s2', 'utility',    'Fiat',          'Strada',  'Freedom',    2020, 54000,  'flex',   'manual',    8900000,  'Campo Grande', false, false],
            ['s1', 'van',        'Fiat',          'Ducato',  'Cargo',      2018, 120000, 'diesel', 'manual',    12500000, 'Dourados',     false, false],
            ['s2', 'truck',      'Mercedes-Benz', 'Accelo',  '1016',       2017, 210000, 'diesel', 'manual',    18000000, 'Campo Grande', false, false],
            ['p',  'motorcycle', 'Yamaha',        'Fazer',   '250',        2019, 27000,  'flex',   'manual',    1950000,  'Bonito',       true,  false],
            ['s3', 'car',        'Hyundai',       'HB20',    'Comfort',    2018, 77000,  'flex',   'manual',    5990000,  'Bonito',       false, false],
            ['p',  'car',        'Jeep',          'Renegade', 'Longitude', 2021, 39000,  'flex',   'automatic', 11900000, 'Campo Grande', false, false],
            ['p',  'car',        'Renault',       'Kwid',    'Zen',        2020, 51000,  'flex',   'manual',    4490000,  'Dourados',     false, false],
            ['s3', 'utility',    'Volkswagen',    'Saveiro', 'Robust',     2019, 83000,  'flex',   'manual',    6900000,  'Bonito',       false, false],
        ];

        $total = count($rows);

        foreach ($rows as $i => [$seller, $category, $brand, $model, $version, $year, $km, $fuel, $transmission, $price, $city, $single, $featured]) {
            $owner = $seller === 'p' ? $this->person($i + 1, $city) : $storeUsers[$seller];

            $vehicle = $owner->vehicles()->create([
                'category' => $category,
                'brand' => $brand,
                'model' => $model,
                'version' => $version,
                'year_manufacture' => $year,
                'year_model' => $year,
                'mileage' => $km,
                'fuel' => $fuel,
                'transmission' => $transmission,
                'color' => 'Variada',
                'single_owner' => $single,
                'price_cents' => $price,
                'city' => $city,
                'state' => 'MS',
                'description' => 'Anuncio de demonstracao.',
            ]);

            $vehicle->forceFill([
                'status' => Vehicle::STATUS_ACTIVE,
                'featured_until' => $featured ? now()->addDays(7) : null,
                'created_at' => now()->subHours(($total - $i) * 5),
            ])->save();

            $this->placeholder($vehicle, "{$brand} {$model}", $i);
        }

        $this->command?->info("{$total} anuncios de demonstracao criados.");
    }

    private function person(int $n, string $city): User
    {
        return User::create([
            'name' => "Vendedor Demo {$n}",
            'email' => "pessoa{$n}@demo.tanagaragem.test",
            'password' => 'demo12345',
            'type' => User::TYPE_PERSON,
            'cpf' => '8' . str_pad((string) $n, 10, '0', STR_PAD_LEFT),
            'phone' => '67999990000',
            'city' => $city,
            'state' => 'MS',
        ]);
    }

    private function placeholder(Vehicle $vehicle, string $label, int $seed): void
    {
        mt_srand($seed + 1);

        $image = imagecreatetruecolor(1200, 800);
        imagefill($image, 0, 0, imagecolorallocate($image, mt_rand(40, 200), mt_rand(40, 200), mt_rand(40, 200)));
        imagestring($image, 5, 40, 40, Str::ascii($label), imagecolorallocate($image, 255, 255, 255));

        ob_start();
        imagejpeg($image, null, 80);
        $binary = (string) ob_get_clean();

        $path = "vehicles/{$vehicle->id}/" . Str::uuid() . '.jpg';
        Storage::disk('public')->put($path, $binary);
        $vehicle->photos()->create(['path' => $path, 'position' => 0]);
    }

    private function cleanup(): void
    {
        User::where('email', 'like', '%@demo.tanagaragem.test')->get()->each(function (User $user) {
            $user->vehicles()->pluck('id')->each(
                fn ($id) => Storage::disk('public')->deleteDirectory("vehicles/{$id}")
            );
            $user->tokens()->delete();
            $user->delete();
        });
    }
}
