<?php

namespace SDamian\Tests\Larasort\Relations\OneToOne_OneToMany\Fixtures\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use SDamian\Larasort\AutoSortable;
use SDamian\Tests\Larasort\Relations\OneToOne_OneToMany\Fixtures\Factories\UserFactory;

class User extends Model
{
    use AutoSortable; // For Larasort

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * For Larasort
     * The attributes that are sortable.
     *
     * @var array<null|string>
     */
    private array $sortables = [
        'id',
        'email',
    ];

    /**
     * The attributes of its sortable relations.
     *
     * @var array<string>
     */
    private array $sortablesRelated = [
        // Convention: {relationship name}{separator}{column in this relationship table}.
        'article.title', // UTILE pour tester la relation "One To One".
        'articles.title', // UTILE pour tester la relation "One To Many".
    ];

    /**
     * UTILE pour tester la relation "One To One".
     */
    public function article(): HasOne
    {
        return $this->hasOne(Article::class, 'user_id_created_at', 'id');
    }

    /**
     * UTILE pour tester la relation "One To Many".
     */
    public function articles(): HasMany
    {
        return $this->hasMany(Article::class, 'user_id_created_at', 'id');
    }

    public static function storeUsers(): void
    {
        $nb = 3;
        for ($i = 1; $i <= $nb; $i++) {
            UserFactory::new()->create([
                'email' => 'user-'.$i.'@gmail.com',
            ]);
        }
    }
}
