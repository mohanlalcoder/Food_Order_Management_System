<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use Illuminate\Database\Seeder;

class MenuItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'name' => 'Margherita Pizza',
                'description' => 'Classic pizza with tomato and mozzarella.',
                'price' => 249.00,
                'image_url' => 'https://images.unsplash.com/photo-1579751626657-72bc17010498?w=600',
            ],
            [
                'name' => 'Classic Burger',
                'description' => 'Juicy burger with lettuce, tomato and cheese.',
                'price' => 179.00,
                'image_url' => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=600',
            ],
            [
                'name' => 'French Fries',
                'description' => 'Golden, crispy potato fries.',
                'price' => 99.00,
                'image_url' => 'https://images.unsplash.com/photo-1573080496219-bb080dd4f877?w=600',
            ],
            [
                'name' => 'Pasta',
                'description' => 'Creamy pasta with herbs and parmesan.',
                'price' => 219.00,
                'image_url' => 'https://images.unsplash.com/photo-1473093295043-cdd812d0e601?w=600',
            ],
            [
                'name' => 'Chocolate Brownie',
                'description' => 'Rich chocolate brownie for dessert.',
                'price' => 119.00,
                'image_url' => 'https://images.unsplash.com/photo-1606313564200-e75d5e30476c?w=600',
            ],
            [
                'name' => 'Fresh drink',
                'description' => 'Refreshing drink.',
                'price' => 79.00,
                'image_url' => 'https://images.unsplash.com/photo-1603569283847-aa295f0d016a?auto=format&fit=crop&w=600&q=80',
            ],
        ];

        foreach ($items as $item) {
            MenuItem::updateOrCreate(
                ['name' => $item['name']],
                [...$item, 'is_available' => true]
            );
        }
    }
}