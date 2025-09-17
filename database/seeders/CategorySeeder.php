<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Sedan',
            'SUV',
            'Hatchback',
            'Coupe',
            'Convertible',
            'Pickup Truck',
            'Minivan',
            'Sports Car',
            'Luxury Car',
            'Electric Vehicle'
        ];

        foreach ($categories as $categoryName) {
            Category::create([
                'name' => $categoryName
            ]);
        }
    }
}
