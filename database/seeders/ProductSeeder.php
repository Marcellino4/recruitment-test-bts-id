<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $jhon = User::where('username', 'jhon_doe')->first();
        $jane = User::where('username', 'jane_doe')->first();

        $products = [
            [
                'title' => 'Awesome T-Shirt',
                'price' => 99.99,
                'description' => 'High-quality cotton t-shirt',
                'category' => 'Clothes',
                'images' => [
                    'https://placeimg.com/640/480/fashion',
                    'https://placeimg.com/640/480/fashion2',
                ],
                'created_by_id' => $jhon->id,
                'updated_by_id' => $jhon->id,
            ],
            [
                'title' => 'Running Shoes Pro',
                'price' => 349.99,
                'description' => 'Lightweight and durable running shoes for all terrains.',
                'category' => 'Footwear',
                'images' => [
                    'https://placeimg.com/640/480/shoes',
                ],
                'created_by_id' => $jane->id,
                'updated_by_id' => $jane->id,
            ],
            [
                'title' => 'Leather Wallet',
                'price' => 149.99,
                'description' => 'Genuine leather slim wallet with RFID protection.',
                'category' => 'Accessories',
                'images' => [
                    'https://placeimg.com/640/480/accessories',
                ],
                'created_by_id' => $jhon->id,
                'updated_by_id' => $jhon->id,
            ],
            [
                'title' => 'Wireless Headphones',
                'price' => 599.99,
                'description' => 'Noise-cancelling Bluetooth headphones with 30-hour battery life.',
                'category' => 'Electronics',
                'images' => [
                    'https://placeimg.com/640/480/tech',
                    'https://placeimg.com/640/480/tech2',
                ],
                'created_by_id' => $jhon->id,
                'updated_by_id' => $jhon->id,
            ],
            [
                'title' => 'Denim Jacket',
                'price' => 299.99,
                'description' => 'Classic denim jacket with modern fit.',
                'category' => 'Clothes',
                'images' => [
                    'https://placeimg.com/640/480/fashion3',
                ],
                'created_by_id' => $jane->id,
                'updated_by_id' => $jane->id,
            ],
            [
                'title' => 'Backpack Urban 30L',
                'price' => 199.99,
                'description' => 'Water-resistant urban backpack with laptop compartment.',
                'category' => 'Bags',
                'images' => [
                    'https://placeimg.com/640/480/any',
                ],
                'created_by_id' => $jhon->id,
                'updated_by_id' => $jhon->id,
            ],
            [
                'title' => 'Smart Watch Series X',
                'price' => 1299.99,
                'description' => 'Advanced smartwatch with health monitoring and GPS.',
                'category' => 'Electronics',
                'images' => [
                    'https://placeimg.com/640/480/tech3',
                ],
                'created_by_id' => $jhon->id,
                'updated_by_id' => $jhon->id,
            ],
            [
                'title' => 'Sunglasses Aviator',
                'price' => 249.99,
                'description' => 'Classic aviator sunglasses with UV400 protection.',
                'category' => 'Accessories',
                'images' => [
                    'https://placeimg.com/640/480/accessories2',
                ],
                'created_by_id' => $jane->id,
                'updated_by_id' => $jane->id,
            ],
            [
                'title' => 'Yoga Mat Premium',
                'price' => 89.99,
                'description' => 'Non-slip premium yoga mat with alignment lines.',
                'category' => 'Sports',
                'images' => [
                    'https://placeimg.com/640/480/sports',
                ],
                'created_by_id' => $jhon->id,
                'updated_by_id' => $jhon->id,
            ],
            [
                'title' => 'Mechanical Keyboard TKL',
                'price' => 799.99,
                'description' => 'Tenkeyless mechanical keyboard with RGB backlight.',
                'category' => 'Electronics',
                'images' => [
                    'https://placeimg.com/640/480/tech4',
                    'https://placeimg.com/640/480/tech5',
                ],
                'created_by_id' => $jhon->id,
                'updated_by_id' => $jhon->id,
            ],
        ];

        foreach ($products as $product) {
            Product::firstOrCreate(
                ['title' => $product['title']],
                $product
            );
        }

        $this->command->info('Products seeded: ' . count($products) . ' records.');
    }
}
