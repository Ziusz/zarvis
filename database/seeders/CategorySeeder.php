<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Sports',
                'description' => 'Book Sports activities and venues',
                'icon' => 'fa-solid fa-basketball',
                'is_featured' => true,
                'children' => [
                    'Basketball',
                    'Football',
                    'Tennis',
                    'Swimming',
                    'Golf',
                ],
            ],
            [
                'name' => 'Fitness',
                'description' => 'Book Fitness classes and trainers',
                'icon' => 'fa-solid fa-dumbbell',
                'is_featured' => true,
                'children' => [
                    'Personal Training',
                    'Group Classes',
                    'Yoga',
                    'Pilates',
                    'CrossFit',
                ],
            ],
            [
                'name' => 'Dance',
                'description' => 'Book Dance classes and studios',
                'icon' => 'fa-solid fa-music',
                'is_featured' => true,
                'children' => [
                    'Ballet',
                    'Hip Hop',
                    'Contemporary',
                    'Ballroom',
                    'Jazz',
                ],
            ],
            [
                'name' => 'Martial Arts',
                'description' => 'Book Martial Arts classes and dojos',
                'icon' => 'fa-solid fa-hand-fist',
                'is_featured' => true,
                'children' => [
                    'Karate',
                    'Judo',
                    'Boxing',
                    'Kickboxing',
                    'MMA',
                ],
            ],
        ];

        foreach ($categories as $categoryData) {
            $children = $categoryData['children'];
            unset($categoryData['children']);

            // Create parent category
            $category = Category::create([
                'name' => $categoryData['name'],
                'slug' => Str::slug($categoryData['name']),
                'description' => $categoryData['description'],
                'icon' => $categoryData['icon'],
                'is_active' => true,
                'is_featured' => $categoryData['is_featured'],
                'sort_order' => 0,
            ]);

            // Create child categories
            foreach ($children as $childName) {
                Category::create([
                    'name' => $childName,
                    'slug' => Str::slug($childName),
                    'description' => "Book {$childName} activities and classes",
                    'icon' => $categoryData['icon'],
                    'parent_id' => $category->id,
                    'is_active' => true,
                    'is_featured' => false,
                    'sort_order' => 0,
                ]);
            }
        }
    }
}
