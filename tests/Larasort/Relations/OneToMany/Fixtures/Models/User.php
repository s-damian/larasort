<?php

namespace SDamian\Tests\Larasort\Relations\OneToMany\Fixtures\Models;

use SDamian\Larasort\AutoSortable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use SDamian\Tests\Larasort\Relations\OneToMany\Fixtures\Factories\UserFactory;

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
        'articles.title',
    ];

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class, 'user_id_created_at', 'id');
    }

    public static function storeUsers(): void
    {
        $nb = 3;
        for ($i=1; $i <= $nb; $i++) {
            UserFactory::new()->create([
                'email' => 'user-'.$i.'@gmail.com',
            ]);
        }
    }
}
