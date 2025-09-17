<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = Category::all();

        if ($categories->count() > 0) {
            $products = [
                // Sedan
                [
                    'name' => 'Toyota Camry 2024', 
                    'quantity' => 15, 
                    'price' => 25000.00, 
                    'category_id' => $categories->where('name', 'Sedan')->first()->id,
                    'description' => 'The 2024 Toyota Camry offers a perfect blend of comfort, reliability, and fuel efficiency. Features include advanced safety systems, spacious interior, and smooth driving experience.'
                ],
                [
                    'name' => 'Honda Accord 2024', 
                    'quantity' => 12, 
                    'price' => 27000.00, 
                    'category_id' => $categories->where('name', 'Sedan')->first()->id,
                    'description' => 'The Honda Accord 2024 combines sophisticated styling with cutting-edge technology. Includes Honda Sensing safety suite, premium interior materials, and excellent fuel economy.'
                ],
                [
                    'name' => 'BMW 3 Series 2024', 
                    'quantity' => 8, 
                    'price' => 45000.00, 
                    'category_id' => $categories->where('name', 'Sedan')->first()->id,
                    'description' => 'The BMW 3 Series delivers the ultimate driving experience with powerful engines, precise handling, and luxurious features. Includes iDrive infotainment system and premium leather interior.'
                ],
                
                // SUV
                [
                    'name' => 'Toyota RAV4 2024', 
                    'quantity' => 20, 
                    'price' => 28000.00, 
                    'category_id' => $categories->where('name', 'SUV')->first()->id,
                    'description' => 'The Toyota RAV4 offers versatile cargo space, all-wheel drive capability, and excellent safety ratings. Perfect for families and outdoor adventures.'
                ],
                [
                    'name' => 'Honda CR-V 2024', 
                    'quantity' => 18, 
                    'price' => 30000.00, 
                    'category_id' => $categories->where('name', 'SUV')->first()->id,
                    'description' => 'The Honda CR-V features a refined interior, advanced safety technology, and excellent fuel efficiency. Ideal for daily commuting and weekend getaways.'
                ],
                [
                    'name' => 'BMW X5 2024', 
                    'quantity' => 10, 
                    'price' => 65000.00, 
                    'category_id' => $categories->where('name', 'SUV')->first()->id,
                    'description' => 'The BMW X5 combines luxury with performance. Features include panoramic sunroof, premium sound system, and advanced driver assistance systems.'
                ],
                
                // Hatchback
                [
                    'name' => 'Volkswagen Golf 2024', 
                    'quantity' => 25, 
                    'price' => 22000.00, 
                    'category_id' => $categories->where('name', 'Hatchback')->first()->id,
                    'description' => 'The Volkswagen Golf offers European styling, excellent handling, and practical cargo space. Perfect for urban driving and weekend trips.'
                ],
                [
                    'name' => 'Honda Civic Hatchback 2024', 
                    'quantity' => 22, 
                    'price' => 24000.00, 
                    'category_id' => $categories->where('name', 'Hatchback')->first()->id,
                    'description' => 'The Honda Civic Hatchback combines sporty styling with practicality. Features include turbocharged engine, spacious interior, and advanced safety systems.'
                ],
                
                // Coupe
                [
                    'name' => 'BMW 4 Series 2024', 
                    'quantity' => 6, 
                    'price' => 52000.00, 
                    'category_id' => $categories->where('name', 'Coupe')->first()->id,
                    'description' => 'The BMW 4 Series offers stunning design, powerful performance, and cutting-edge technology. Includes M Sport package and premium interior features.'
                ],
                [
                    'name' => 'Mercedes-Benz C-Class Coupe 2024', 
                    'quantity' => 5, 
                    'price' => 58000.00, 
                    'category_id' => $categories->where('name', 'Coupe')->first()->id,
                    'description' => 'The Mercedes-Benz C-Class Coupe delivers luxury and performance in a sleek package. Features include MBUX infotainment system and premium materials.'
                ],
                
                // Convertible
                [
                    'name' => 'BMW Z4 2024', 
                    'quantity' => 4, 
                    'price' => 55000.00, 
                    'category_id' => $categories->where('name', 'Convertible')->first()->id,
                    'description' => 'The BMW Z4 offers open-air driving pleasure with powerful engines and precise handling. Perfect for weekend drives and special occasions.'
                ],
                [
                    'name' => 'Mercedes-Benz SL-Class 2024', 
                    'quantity' => 3, 
                    'price' => 120000.00, 
                    'category_id' => $categories->where('name', 'Convertible')->first()->id,
                    'description' => 'The Mercedes-Benz SL-Class represents the pinnacle of luxury convertibles. Features include retractable hardtop, premium interior, and advanced technology.'
                ],
                
                // Pickup Truck
                [
                    'name' => 'Ford F-150 2024', 
                    'quantity' => 30, 
                    'price' => 35000.00, 
                    'category_id' => $categories->where('name', 'Pickup Truck')->first()->id,
                    'description' => 'The Ford F-150 is America\'s best-selling truck for over 40 years. Offers powerful towing capacity, spacious interior, and advanced technology features.'
                ],
                [
                    'name' => 'Chevrolet Silverado 2024', 
                    'quantity' => 28, 
                    'price' => 38000.00, 
                    'category_id' => $categories->where('name', 'Pickup Truck')->first()->id,
                    'description' => 'The Chevrolet Silverado combines rugged capability with modern comfort. Features include advanced trailering technology and spacious crew cab.'
                ],
                
                // Minivan
                [
                    'name' => 'Honda Odyssey 2024', 
                    'quantity' => 15, 
                    'price' => 32000.00, 
                    'category_id' => $categories->where('name', 'Minivan')->first()->id,
                    'description' => 'The Honda Odyssey is perfect for families with its spacious interior, versatile seating, and excellent safety features. Includes entertainment system for rear passengers.'
                ],
                [
                    'name' => 'Toyota Sienna 2024', 
                    'quantity' => 12, 
                    'price' => 34000.00, 
                    'category_id' => $categories->where('name', 'Minivan')->first()->id,
                    'description' => 'The Toyota Sienna offers hybrid powertrain, all-wheel drive capability, and premium interior features. Ideal for family transportation.'
                ],
                
                // Sports Car
                [
                    'name' => 'Porsche 911 2024', 
                    'quantity' => 3, 
                    'price' => 120000.00, 
                    'category_id' => $categories->where('name', 'Sports Car')->first()->id,
                    'description' => 'The Porsche 911 is the ultimate sports car, offering unmatched performance, precision engineering, and iconic design. Includes advanced aerodynamics and premium interior.'
                ],
                [
                    'name' => 'Ferrari F8 Tributo 2024', 
                    'quantity' => 1, 
                    'price' => 280000.00, 
                    'category_id' => $categories->where('name', 'Sports Car')->first()->id,
                    'description' => 'The Ferrari F8 Tributo represents the pinnacle of automotive engineering. Features include mid-engine layout, carbon fiber construction, and race-inspired aerodynamics.'
                ],
                
                // Luxury Car
                [
                    'name' => 'Mercedes-Benz S-Class 2024', 
                    'quantity' => 5, 
                    'price' => 110000.00, 
                    'category_id' => $categories->where('name', 'Luxury Car')->first()->id,
                    'description' => 'The Mercedes-Benz S-Class sets the standard for luxury sedans. Features include ambient lighting, massage seats, and advanced driver assistance systems.'
                ],
                [
                    'name' => 'BMW 7 Series 2024', 
                    'quantity' => 4, 
                    'price' => 95000.00, 
                    'category_id' => $categories->where('name', 'Luxury Car')->first()->id,
                    'description' => 'The BMW 7 Series offers executive luxury with cutting-edge technology. Includes gesture control, premium sound system, and advanced comfort features.'
                ],
                
                // Electric Vehicle
                [
                    'name' => 'Tesla Model 3 2024', 
                    'quantity' => 35, 
                    'price' => 42000.00, 
                    'category_id' => $categories->where('name', 'Electric Vehicle')->first()->id,
                    'description' => 'The Tesla Model 3 offers electric performance with long range capability. Features include Autopilot, over-the-air updates, and minimalist interior design.'
                ],
                [
                    'name' => 'Tesla Model Y 2024', 
                    'quantity' => 30, 
                    'price' => 48000.00, 
                    'category_id' => $categories->where('name', 'Electric Vehicle')->first()->id,
                    'description' => 'The Tesla Model Y combines SUV versatility with electric efficiency. Features include panoramic glass roof, spacious cargo area, and advanced safety systems.'
                ],
                [
                    'name' => 'Ford Mustang Mach-E 2024', 
                    'quantity' => 25, 
                    'price' => 45000.00, 
                    'category_id' => $categories->where('name', 'Electric Vehicle')->first()->id,
                    'description' => 'The Ford Mustang Mach-E delivers electric performance with Mustang heritage. Features include all-wheel drive capability, spacious interior, and advanced connectivity.'
                ],
            ];

            foreach ($products as $product) {
                Product::create($product);
            }
        }
    }
}
