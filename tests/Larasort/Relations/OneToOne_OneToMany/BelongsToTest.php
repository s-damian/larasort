<?php

namespace SDamian\Tests\Larasort\Relations\OneToOne_OneToMany;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Request;
use SDamian\Tests\Larasort\Relations\OneToOne_OneToMany\Fixtures\Models\Article;
use SDamian\Tests\Larasort\Relations\OneToOne_OneToMany\Fixtures\Models\User;
use SDamian\Tests\Larasort\Relations\OneToOne_OneToMany\Traits\ForOneToOneTrait;
use SDamian\Tests\Larasort\Traits\ForAllTestsTrait;
use SDamian\Tests\TestCase;

/**
 * Ici on test le "Belongs To".
 * Le "Belongs To" fonctionne identiquement avec "One To One" et avec "One To Many".
 * Dans cette class de test, on va le "simuler" avec les mêmes données en BDD qu'avec le "One To One".
 *
 * Ici on "simule" qu'on est dans un Controller Article,
 * et qu'on travail le "Belongs To" sur un Model Article qui a une relation "One To One" avec un Model User.
 * Un article peut avoir été créé par qu'un seul user, un user peut avoir créé qu'un seul article.
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

        // Spécifique pour "Belongs To"
        $article1 = Article::find(1);
        $this->assertTrue($article1->user instanceof User); // On en profite pour tester la méthode de relation "user".
    }

    /*
    |--------------------------------------------------------------------------
    | On test les req SQL (sans tester les order by)
    |--------------------------------------------------------------------------
    */

    /**
     * On test que la req SQL JOIN de Larasort retourne bien le même résultat que la req SQL JOIN de Eloquent.
     */
    public function test_join(): void
    {
        $this->verifyInAllTests();

        $articles = Article::autosortWith('user', [
            'join_type' => 'join', // Optional - "leftJoin" by default.
        ])
            ->get();

        $articlesB = Article::select('articles.title')
            ->join('users', 'articles.user_id_created_at', '=', 'users.id')
            ->get();

        // Seul 2 des 3 users sont joint à un article.
        $this->assertSame(2, $articles->count());
        $this->assertSame($articlesB->count(), $articles->count());
    }

    /**
     * On test que la req SQL LEFT JOIN de Larasort retourne bien le même résultat que la req SQL LEFT JOIN de Eloquent.
     */
    public function test_left_join(): void
    {
        $this->verifyInAllTests();

        $articles = Article::autosortWith('user', [
            'join_type' => 'leftJoin', // Optional - "leftJoin" by default.
        ])
            ->get();

        $articlesB = Article::select('articles.title')
            ->leftJoin(
                'users',
                'articles.user_id_created_at',
                '=',
                'users.id'
            )
            ->get();

        // Seul 2 des 3 users sont joint à un article, mais avec le "leftJoin" ça retourne tout de même 3 lignes (car il y a 3 articles dans la BDD).
        $this->assertSame(3, $articles->count());
        $this->assertSame($articlesB->count(), $articles->count());
    }

    /*
    |--------------------------------------------------------------------------
    | On test les order by
    |--------------------------------------------------------------------------
    */

    public function test_with_order_by_article_title(): void
    {
        $this->verifyInAllTests();

        Request::offsetSet('orderby', 'title'); // ICI
        Request::offsetSet('order', 'asc');

        // Tester avec JOIN :

        $articles = $this->getArticlesJoinToUsers();

        $articleFirst = $articles->first();
        $articleLast = $articles->last();

        // Req avec JOIN retourne 2 rows :
        // "Article 1" et "Article 3". Car seul ces 2 articles sont joint à un user.
        $this->assertSame(2, $articles->count());
        $this->assertSame('Title-1', $articleFirst->title);
        $this->assertSame('Title-3', $articleLast->title); // ICI

        // Tester avec LEFT JOIN :

        $articles = $this->getArticlesLeftJoinToUsers();

        $articleFirst = $articles->first();
        $articleLast = $articles->last();

        // Req avec LEFT JOIN retourne les 3 rows.
        $this->assertSame(3, $articles->count());
        $this->assertSame('Title-1', $articleFirst->title);
        $this->assertSame('Title-3', $articleLast->title); // ICI
    }

    public function test_with_order_by_user_email_asc(): void
    {
        $this->verifyInAllTests();

        Request::offsetSet('orderby', 'user.email'); // ICI
        Request::offsetSet('order', 'asc'); // ICI

        // Tester avec JOIN :

        $articles = $this->getArticlesJoinToUsers();

        $articleFirst = $articles->first();
        $articleLast = $articles->last();

        // Req avec JOIN retourne 2 rows :
        // "Article 1" et "Article 3". Car seul ces 2 articles sont joint à un user.
        $this->assertSame(2, $articles->count());
        $this->assertSame('Title-1', $articleFirst->title); // Car "Article 1" est joint à "user 1".
        $this->assertSame('Title-3', $articleLast->title); // Car "Article 3" est joint à "user 2".

        // Tester avec LEFT JOIN :

        $articles = $this->getArticlesLeftJoinToUsers();

        $articleFirst = $articles->first();
        $articleLast = $articles->last();

        // Req avec LEFT JOIN retourne les 3 rows.
        $this->assertSame(3, $articles->count());
        $this->assertSame('Title-2', $articleFirst->title); // Car "Article 2" n'a pas d'user joint, donc sont 'user_email' vaut null.
        $this->assertSame('Title-3', $articleLast->title); // Car "Article 3" est joint à "user 2".
    }

    public function test_with_order_by_user_email_desc(): void
    {
        $this->verifyInAllTests();

        Request::offsetSet('orderby', 'user.email'); // ICI
        Request::offsetSet('order', 'desc'); // ICI

        // Tester avec JOIN :

        $articles = $this->getArticlesJoinToUsers();

        $articleFirst = $articles->first();
        $articleLast = $articles->last();

        // Req avec JOIN retourne 2 rows :
        // "Article 1" et "Article 3". Car seul ces 2 articles sont joint à un user.
        $this->assertSame(2, $articles->count());
        $this->assertSame('Title-3', $articleFirst->title); // Car "Article 3" est joint à "user 2".
        $this->assertSame('Title-1', $articleLast->title); // Car "Article 1" est joint à "user 1".

        // Tester avec LEFT JOIN :

        $articles = $this->getArticlesLeftJoinToUsers();

        $articleFirst = $articles->first();
        $articleLast = $articles->last();

        // Req avec LEFT JOIN retourne les 3 rows.
        $this->assertSame(3, $articles->count());
        $this->assertSame('Title-3', $articleFirst->title); // Car "Article 3" est joint à "user 2".
        $this->assertSame('Title-2', $articleLast->title); // Car "Article 2" n'a pas d'user joint, donc sont 'user_email' vaut null.
    }

    /*
    |--------------------------------------------------------------------------
    | On "range" les req SQL qu'on a besoin dans plusieurs tests
    |--------------------------------------------------------------------------
    */

    private function getArticlesJoinToUsers(): Collection
    {
        return Article::autosortWith('user', [
            'join_type' => 'join', // Optional - "leftJoin" by default.
            'columns' => 'id, title, content', // Optional - "*" by default.
            'related_columns' => 'email AS user_email, username', // Optional -"*" by default.
        ])
            ->get();
    }

    private function getArticlesLeftJoinToUsers(): Collection
    {
        return Article::autosortWith('user', [
            'join_type' => 'leftJoin', // Optional - "leftJoin" by default.
            'columns' => ['id', 'title', 'content'], // Optional - "*" by default.
            'related_columns' => ['email AS user_email', 'username'], // Optional -"*" by default.
        ])
            ->get();
    }
}
