<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = \Faker\Factory::create();

        DB::transaction(function () use ($faker): void {
            for ($i = 0; $i < 10; $i++) {
                Product::create([
                    'name'        => $faker->words(2, true) . ' ' . \ucfirst($faker->word()),
                    'price'       => $faker->randomFloat(2, 9.99, 999.99),
                    'description' => $faker->paragraph(3),
                ]);
            }
        });

        $this->command->info('10 products seeded successfully!');
    }
}
