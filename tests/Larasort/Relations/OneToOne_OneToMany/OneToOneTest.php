<?php

namespace SDamian\Tests\Larasort\Relations\OneToOne_OneToMany;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Request;
use SDamian\Larasort\LarasortLink;
use SDamian\Tests\Larasort\Relations\OneToOne_OneToMany\Fixtures\Models\Article;
use SDamian\Tests\Larasort\Relations\OneToOne_OneToMany\Fixtures\Models\User;
use SDamian\Tests\Larasort\Relations\OneToOne_OneToMany\Traits\ForOneToOneTrait;
use SDamian\Tests\Larasort\Traits\ForAllTestsTrait;
use SDamian\Tests\TestCase;

/**
 * Ici on test le "One To One".
 *
 * Ici on "simule" qu'on est dans un Controller User,
 * et qu'on travail sur un Model User qui a une relation "One To One" avec un Model Article.
 * Un user peut avoir créé qu'un seul article, un article peut avoir été créé par qu'un seul user.
 */
class OneToOneTest extends TestCase
{
    use ForAllTestsTrait;
    use ForOneToOneTrait;

    public function setUp(): void
    {
        parent::setUp();

        // ***** On prépare la BDD (migrations, etc.). *****
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

    /**
     * On test que la req SQL JOIN de Larasort retourne bien le même résultat que la req SQL JOIN de Eloquent.
     */
    public function test_join(): void
    {
        $this->verifyInAllTests();

        $users = User::autosortWith('article', [
            'join_type' => 'join', // Optional - "leftJoin" by default.
        ])
            ->get();

        $usersB = User::select('users.email')
            ->join('articles', 'users.id', '=', 'articles.user_id_created_at')
            ->get();

        // Seul 2 des 3 users sont joint à un article.
        $this->assertSame(2, $users->count());
        $this->assertSame($usersB->count(), $users->count());
    }

    /**
     * On test que la req SQL LEFT JOIN de Larasort retourne bien le même résultat que la req SQL LEFT JOIN de Eloquent.
     */
    public function test_left_join(): void
    {
        $this->verifyInAllTests();

        $users = User::autosortWith('article', [
            'join_type' => 'leftJoin', // Optional - "leftJoin" by default.
        ])
            ->get();

        $usersB = User::select('users.email')
            ->leftJoin(
                'articles',
                'users.id',
                '=',
                'articles.user_id_created_at'
            )
            ->get();

        // Seul 2 des 3 users sont joint à un article, mais avec le "leftJoin" ça retourne tout de même 3 lignes (car il y a 3 articles dans la BDD).
        $this->assertSame(3, $users->count());
        $this->assertSame($usersB->count(), $users->count());
    }

    /*
    |--------------------------------------------------------------------------
    | On test les order by
    |--------------------------------------------------------------------------
    */

    public function test_with_order_by_user_email(): void
    {
        $this->verifyInAllTests();

        Request::offsetSet('orderby', 'email'); // ICI
        Request::offsetSet('order', 'asc');

        // Tester avec JOIN :

        $users = $this->getUsersJoinToArticles();

        $userFirst = $users->first();
        $userLast = $users->last();

        // Req avec JOIN retourne 2 rows :
        // 1 fois l'user "user-1" car il est joint à 1 article.
        // 1 fois l'user "user-2" car il est joint à 1 article.
        $this->assertSame(2, $users->count());
        $this->assertSame('user-1@gmail.com', $userFirst->email);
        $this->assertSame('user-2@gmail.com', $userLast->email); // ICI

        // Tester avec LEFT JOIN :

        $users = $this->getUsersLeftJoinToArticles();

        $userFirst = $users->first();
        $userLast = $users->last();

        // Req avec LEFT JOIN retourne 3 rows :
        // 1 fois l'user "user-1" car il est joint à 1 article.
        // 1 fois l'user "user-2" car il est joint à 1 article.
        // 1 fois l'user "user-3" car ici on fait un "leftJoin".
        $this->assertSame(3, $users->count());
        $this->assertSame('user-1@gmail.com', $userFirst->email);
        $this->assertSame('user-3@gmail.com', $userLast->email); // ICI
    }

    public function test_with_order_by_article_title_asc(): void
    {
        $this->verifyInAllTests();

        Request::offsetSet('orderby', 'article.title'); // ICI
        Request::offsetSet('order', 'asc'); // ICI

        // Tester avec JOIN :

        $users = $this->getUsersJoinToArticles();

        $userFirst = $users->first();
        $userLast = $users->last();

        // Req avec JOIN retourne 2 rows :
        // 1 fois l'user "user-1" car il est joint à 1 article.
        // 1 fois l'user "user-2" car il est joint à 1 article.
        $this->assertSame(2, $users->count());
        $this->assertSame('user-1@gmail.com', $userFirst->email); // ICI - "user-1" est joint à l'article 1 (le premier article)
        $this->assertSame('user-2@gmail.com', $userLast->email); // ICI - "user-2" est joint à l'article 3 (le dernier article)

        // Tester avec LEFT JOIN :

        $users = $this->getUsersLeftJoinToArticles();

        $userFirst = $users->first();
        $userLast = $users->last();

        // Req avec LEFT JOIN retourne 3 rows :
        // 1 fois l'user "user-1" car il est joint à 1 article.
        // 1 fois l'user "user-2" car il est joint à 1 article.
        // 1 fois l'user "user-3" car ici on fait un "leftJoin".
        $this->assertSame(3, $users->count());
        $this->assertSame('user-3@gmail.com', $userFirst->email); // ICI - "user-3" est joint à AUCUN article (donc son title vaut null)
        $this->assertSame('user-2@gmail.com', $userLast->email); // ICI - "user-2" est joint à l'article 3 (le dernier article)
    }

    public function test_with_order_by_article_title_desc(): void
    {
        $this->verifyInAllTests();

        Request::offsetSet('orderby', 'article.title'); // ICI
        Request::offsetSet('order', 'desc'); // ICI

        // Tester avec JOIN :

        $users = $this->getUsersJoinToArticles();

        $userFirst = $users->first();
        $userLast = $users->last();

        // Req avec JOIN retourne 2 rows :
        // 1 fois l'user "user-1" car il est joint à 1 article.
        // 1 fois l'user "user-2" car il est joint à 1 article.
        $this->assertSame(2, $users->count());
        $this->assertSame('user-2@gmail.com', $userFirst->email); // ICI - "user-2" est joint à l'article 3 (le dernier article)
        $this->assertSame('user-1@gmail.com', $userLast->email); // ICI - "user-1" est joint à l'article 1 (le premier article)

        // Tester avec LEFT JOIN :

        $users = $this->getUsersLeftJoinToArticles();

        $userFirst = $users->first();
        $userLast = $users->last();

        // Req avec LEFT JOIN retourne 3 rows :
        // 1 fois l'user "user-1" car il est joint à 1 article.
        // 1 fois l'user "user-2" car il est joint à 1 article.
        // 1 fois l'user "user-3" car ici on fait un "leftJoin".
        $this->assertSame(3, $users->count());
        $this->assertSame('user-2@gmail.com', $userFirst->email); // ICI - "user-2" est joint à l'article 3 (le dernier article)
        $this->assertSame('user-3@gmail.com', $userLast->email); // ICI - "user-3" est joint à AUCUN article (donc son title vaut null)
    }

    /*
    |--------------------------------------------------------------------------
    | Tester LarasortLink::getLink avec une relation
    |--------------------------------------------------------------------------
    */

    public function test_larasort_link_get_link_with_relation(): void
    {
        // Et on en profite pour test la méthode "getLink" (SANS passer de label, AVEC une colonne qui contient le SEPARATOR) :

        Request::offsetSet('orderby', 'article'.config('larasort.relation_column_separator').'title');
        Request::offsetSet('order', 'desc');

        $this->assertSame(
            '<a href="http://localhost/?orderby=article'.config('larasort.relation_column_separator').'title&order=asc">Article title<span class="larasort-icon-2"></span></a>',
            LarasortLink::getLink('article'.config('larasort.relation_column_separator').'title')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | On "range" les req SQL qu'on a besoin dans plusieurs tests
    |--------------------------------------------------------------------------
    */

    private function getUsersJoinToArticles(): Collection
    {
        return User::autosortWith('article', [
            'join_type' => 'join', // Optional - "leftJoin" by default.
            'columns' => 'id, email, username', // Optional - "*" by default.
            'related_columns' => 'title AS article_title, content', // Optional -"*" by default.
        ])
            ->get();
    }

    private function getUsersLeftJoinToArticles(): Collection
    {
        return User::autosortWith('article', [
            'join_type' => 'leftJoin', // Optional - "leftJoin" by default.
            'columns' => ['id', 'email', 'username'], // Optional - "*" by default.
            'related_columns' => ['title AS article_title', 'content'], // Optional -"*" by default.
        ])
            ->get();
    }
}
