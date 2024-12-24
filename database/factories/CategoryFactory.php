<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->words(2, true);
        
        return [
            'name' => ucwords($name),
            'slug' => Str::slug($name),
            'description' => $this->faker->paragraph(),
            'icon' => 'fa-' . $this->faker->word(),
            'image' => $this->faker->imageUrl(640, 480, 'sports'),
            'is_active' => $this->faker->boolean(90), // 90% chance of being active
            'is_featured' => $this->faker->boolean(20), // 20% chance of being featured
            'sort_order' => $this->faker->numberBetween(0, 100),
            'metadata' => json_encode([
                'seo_title' => $this->faker->sentence(),
                'seo_description' => $this->faker->paragraph(),
                'seo_keywords' => $this->faker->words(5),
            ]),
        ];
    }

    /**
     * Indicate that the category is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the category is featured.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }

    /**
     * Create a root category.
     */
    public function root(): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => null,
        ]);
    }

    /**
     * Create a child category.
     */
    public function child(Category $parent = null): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => $parent ? $parent->id : Category::factory()->create()->id,
        ]);
    }
}
