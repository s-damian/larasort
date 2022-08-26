<?php

namespace SDamian\Tests\Larasort\Relations\OneToOne_OneToMany;

use SDamian\Tests\TestCase;
use Illuminate\Support\Facades\Request;
use Illuminate\Database\Eloquent\Collection;
use SDamian\Tests\Larasort\Utils\ForAllTestsTrait;
use SDamian\Tests\Larasort\Relations\OneToOne_OneToMany\Fixtures\Models\User;
use SDamian\Tests\Larasort\Relations\OneToOne_OneToMany\Fixtures\Models\Article;
use SDamian\Tests\Larasort\Relations\OneToOne_OneToMany\Traits\ForOneToOneTrait;

/**
 * Ici on test le "Belongs To".
 * Le "Belongs To" fonctionne identiquement avec "One To One" et avec "One To Many".
 * Dans cette class de test, on va le "simuler" avec les mêmes données en BDD qu'avec le "One To One".
 */
class BelongsToTest extends TestCase
{
    use ForAllTestsTrait;
    use ForOneToOneTrait;

    public function setUp(): void
    {
        parent::setUp();

        // ***** On prépare la BDD (migrations, etc.). *****
        // On prépare la BDD (migrations, etc.).
        // Pour ces tests, on veut les contraintes de clés étrangères.
        config(['database.connections.testing.foreign_key_constraints' => true]);

        if (getenv('DB_CONNECTION') !== 'testing' || config('database.connections.testing.driver') !== 'sqlite') {
            $this->markTestSkipped('Test requires a Sqlite connection.');
        }

        // Créer la table "customers", et y insert 3 rows :

        $this->artisan('migrate', [
            '--path' => realpath(__DIR__.'/Fixtures/migrations/'),
            '--realpath' => true,
        ]);

        User::storeUsers();

        Article::storeArticles_forOneToOne();
        // ***** /On prépare la BDD (migrations, etc.). *****

        // On a besoin de ces déonnes pour nos tests de cette class
        $this->verifyDataInDb();
    }

    /*
    |--------------------------------------------------------------------------
    | On test les req SQL (sans tester les order by)
    |--------------------------------------------------------------------------
    */

    public function testA(): void
    {
        $this->verifyInAllTests();

        $articles = Article::autosort([
                'related' => 'user', // Required - name of the relation.
                'join_type' => 'join', // Optional - "leftJoin" by default.
                'columns' => ['id', 'title', 'content'], // Optional - "*" by default.
                'related_columns' => ['email AS user_email', 'username'], // Optional -"*" by default.
            ])
            ->get();

        //dd( $articles );
    }
}
