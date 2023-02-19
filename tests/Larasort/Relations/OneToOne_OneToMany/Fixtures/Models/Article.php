<?php

namespace SDamian\Tests\Larasort\Relations\OneToOne_OneToMany\Fixtures\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use SDamian\Larasort\AutoSortable;
use SDamian\Tests\Larasort\Relations\OneToOne_OneToMany\Fixtures\Factories\ArticleFactory;

class Article extends Model
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
        'title',
    ];

    /**
     * The attributes of its sortable relations.
     *
     * @var array<string>
     */
    private array $sortablesRelated = [
        // Convention: {relationship name}{separator}{column in this relationship table}.
        'user.email', // UTILE pour tester le "Belongs To".
    ];

    /**
     * UTILE pour tester la relation "Belongs To".
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id_created_at', 'id');
    }

    /**
     * UTILE pour tester la relation "One To One".
     */
    public static function storeArticles_forOneToOne(): void
    {
        $nb = 3;
        for ($i = 1; $i <= $nb; $i++) {
            switch ($i) {
                case 1:
                    $user_id_created_at = 1;

                    break;
                case 2:
                    $user_id_created_at = null;

                    break;
                case 3:
                    $user_id_created_at = 2;

                    break;
            }

            ArticleFactory::new()->create([
                'user_id_created_at' => $user_id_created_at ?? null,
                'title' => 'Title-'.$i,
            ]);
        }
    }

    /**
     * UTILE pour tester la relation "One To Many".
     */
    public static function storeArticles_forOneToMany(): void
    {
        $nb = 5;
        for ($i = 1; $i <= $nb; $i++) {
            switch ($i) {
                case 1:
                    $user_id_created_at = 1;

                    break;
                case 2:
                    $user_id_created_at = 1;

                    break;
                case 3:
                    $user_id_created_at = null;

                    break;
                case 4:
                    $user_id_created_at = 2;

                    break;
                case 5:
                    $user_id_created_at = 1;

                    break;
            }

            ArticleFactory::new()->create([
                'user_id_created_at' => $user_id_created_at ?? null,
                'title' => 'Title-'.$i,
            ]);
        }
    }
}
