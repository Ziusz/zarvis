<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * The main categories with their subcategories.
     */
    protected array $categories = [
        'Sports' => [
            'Tennis',
            'Basketball',
            'Swimming',
            'Football',
            'Volleyball',
            'Badminton',
            'Golf',
            'Squash',
            'Ice Skating',
            'Martial Arts',
            'Rock Climbing',
            'Skateboarding',
            'Surfing',
            'Skiing/Snowboarding',
            'Horse Riding',
        ],
        'Training & Fitness' => [
            'Personal Training',
            'Group Training',
            'Yoga',
            'CrossFit',
            'Pilates',
            'Boxing',
            'Dance',
            'Gymnastics',
            'Athletic Training',
            'Sports Nutrition',
            'Performance Training',
        ],
        'Entertainment' => [
            'Escape Rooms',
            'Bowling',
            'Laser Tag',
            'Arcade Games',
            'Mini Golf',
            'Billiards/Pool',
            'Karaoke',
            'VR Gaming',
            'Board Games',
        ],
        'Wellness' => [
            'Spa & Massage',
            'Sauna',
            'Meditation',
            'Float Tanks',
            'Hot Springs',
            'Physiotherapy',
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ($this->categories as $mainCategory => $subCategories) {
            // Create main category
            $parent = Category::create([
                'name' => $mainCategory,
                'slug' => Str::slug($mainCategory),
                'description' => "Book {$mainCategory} activities and venues",
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 0,
                'metadata' => [
                    'seo_title' => "{$mainCategory} Booking - Find and Book {$mainCategory} Activities",
                    'seo_description' => "Discover and book the best {$mainCategory} activities and venues in your area. Easy online booking, instant confirmation.",
                    'seo_keywords' => "book {$mainCategory}, {$mainCategory} booking, {$mainCategory} activities",
                ],
            ]);

            // Create subcategories
            foreach ($subCategories as $index => $subCategory) {
                Category::create([
                    'name' => $subCategory,
                    'slug' => Str::slug($subCategory),
                    'description' => "Book {$subCategory} activities and venues",
                    'parent_id' => $parent->id,
                    'is_active' => true,
                    'is_featured' => false,
                    'sort_order' => $index,
                    'metadata' => [
                        'seo_title' => "{$subCategory} Booking - Find and Book {$subCategory} Activities",
                        'seo_description' => "Discover and book {$subCategory} activities and venues in your area. Easy online booking, instant confirmation.",
                        'seo_keywords' => "book {$subCategory}, {$subCategory} booking, {$subCategory} activities",
                    ],
                ]);
            }
        }
    }
}
