<?php

namespace SDamian\Tests\Larasort\Relations\OneToOne_OneToMany\Fixtures\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use SDamian\Tests\Larasort\Relations\OneToOne_OneToMany\Fixtures\Models\Article;

class ArticleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Article::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id_created_at' => null,
            'title' => $this->faker->unique()->title,
            'description' => 'Description',
            'content' => 'Content',
        ];
    }
}
