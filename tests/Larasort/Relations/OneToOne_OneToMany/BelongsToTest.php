<?php

namespace SDamian\Tests\Larasort\Relations\OneToOne_OneToMany;

use SDamian\Tests\TestCase;
use Illuminate\Support\Facades\Request;
use Illuminate\Database\Eloquent\Collection;
use SDamian\Tests\Larasort\Utils\ForAllTestsTrait;
use SDamian\Tests\Larasort\Relations\OneToOne_OneToMany\Fixtures\Models\User;
use SDamian\Tests\Larasort\Relations\OneToOne_OneToMany\Fixtures\Models\Article;

/**
 * Ici on test le "Belongs To".
 * Le "Belongs To" fonctionne identiquement avec "One To One" et avec "One To Many".
 * Dans cette class de test, on va le "simuler" avec "One To Many".
 */
class BelongsToTest extends TestCase
{
    use ForAllTestsTrait;

    public function setUp(): void
    {
        parent::setUp();

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

        Article::storeArticles_forOneToMany();

        // On a besoin de ces déonnes pour nos tests de cette class
        $this->verifyDataInDb();
    }

    /**
     * Pour tester la relation One To Many, on insert 5 articles et 3 users.
     * On veut :
     * - 3 articles joints à "$user1".
     * - 1 article joint à "$user2".
     * - 0 article joint à "$user3".
     * - et le 3è article n'aura pas de user joint..
     */
    private function verifyDataInDb(): void
    {
        $this->assertSame(3, User::count());
        $this->assertSame(5, Article::count());

        $user1 = User::find(1);
        $this->assertSame(3, $user1->articles()->count());

        $this->assertTrue($user1->articles instanceof Collection); // On en profite pour tester la méthode de relation "articles".

        $user2 = User::find(2);
        $this->assertSame(1, $user2->articles()->count());

        $user3 = User::find(3);
        $this->assertSame(0, $user3->articles()->count());

        $article3 = Article::find(3);
        $this->assertTrue($article3->user_id_created_at === null);
    }

    /*
    |--------------------------------------------------------------------------
    | On test les req SQL (sans tester les order by)
    |--------------------------------------------------------------------------
    */

    public function testA(): void
    {
        $this->verifyInAllTests();
    }
}
