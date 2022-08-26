<?php

namespace SDamian\Tests\Larasort\Relations\OneToOne_OneToMany;

use SDamian\Tests\TestCase;
use Illuminate\Support\Facades\Request;
use Illuminate\Database\Eloquent\Collection;
use SDamian\Tests\Larasort\Utils\ForAllTestsTrait;
use SDamian\Tests\Larasort\Relations\OneToOne_OneToMany\Fixtures\Models\User;
use SDamian\Tests\Larasort\Relations\OneToOne_OneToMany\Fixtures\Models\Article;

class OneToOneTest extends TestCase
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

        Article::storeArticles_forOneToOne();

        // On a besoin de ces déonnes pour nos tests de cette class
        $this->verifyDataInDb();
    }

    /**
     * Pour tester la relation One To One, on insert 3 articles et 3 users.
     * On veut :
     * - 1 article joint à "$user1".
     * - 1 article joint à "$user2".
     * - et le 2è article n'aura pas de user joint..
     */
    private function verifyDataInDb(): void
    {
        $this->assertSame(3, User::count());
        $this->assertSame(3, Article::count());

        $user1 = User::find(1);
        $this->assertSame(1, $user1->articles()->count());

        $this->assertTrue($user1->article instanceof Article); // On en profite pour tester la méthode de relation "article".

        $user2 = User::find(2);
        $this->assertSame(1, $user2->articles()->count());

        $user3 = User::find(3);
        $this->assertSame(0, $user3->articles()->count());

        $article2 = Article::find(2);
        $this->assertTrue($article2->user_id_created_at === null);
    }

    /*
    |--------------------------------------------------------------------------
    | On test les req SQL (sans tester les order by)
    |--------------------------------------------------------------------------
    */

    /**
     * On test que la req SQL JOIN de Larasort retourne bien le même résultat que la req SQL JOIN de Eloquent.
     */
    public function testJoin(): void
    {
        $this->verifyInAllTests();

        $users = User::autosort([
                'related' => 'article', // Required - name of the relation.
                'join_type' => 'join', // Optional - "leftJoin" by default.
            ])
            ->get();

        $usersB = User::select('users.email')
            ->join(
                'articles',
                'users.id',
                '=',
                'articles.user_id_created_at'
            )
            ->get();

        // Seul 2 des 3 users sont joint à un article.
        $this->assertSame(2, $users->count());
        $this->assertSame($usersB->count(), $users->count());
    }

    /**
     * On test que la req SQL LEFT JOIN de Larasort retourne bien le même résultat que la req SQL LEFT JOIN de Eloquent.
     */
    public function testLeftJoin(): void
    {
        $this->verifyInAllTests();

        $users = User::autosort([
                'related' => 'article', // Required - name of the relation.
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

    public function testWithOrderByUserEmail(): void
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

    public function testWithOrderByArticleTitleAsc(): void
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

    public function testWithOrderByArticleTitleDesc(): void
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
    | On "range" les req SQL qu'on a besoin dans plusieurs tests
    |--------------------------------------------------------------------------
    */

    private function getUsersJoinToArticles(): Collection
    {
        return User::autosort([
                'related' => 'article', // Required - name of the relation.
                'join_type' => 'join', // Optional - "leftJoin" by default.
                'columns' => ['id', 'email', 'username'], // Optional - "*" by default.
                'related_columns' => ['title AS article_title', 'content'], // Optional -"*" by default.
            ])
            ->get();
    }

    private function getUsersLeftJoinToArticles(): Collection
    {
        return User::autosort([
                'related' => 'article', // Required - name of the relation.
                'join_type' => 'leftJoin', // Optional - "leftJoin" by default.
                'columns' => ['id', 'email', 'username'], // Optional - "*" by default.
                'related_columns' => ['title AS article_title', 'content'], // Optional -"*" by default.
            ])
            ->get();
    }
}