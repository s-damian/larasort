<?php

namespace SDamian\Tests\Larasort\Relations\OneToOne_OneToMany\Traits;

use SDamian\Tests\Larasort\Relations\OneToOne_OneToMany\Fixtures\Models\Article;
use SDamian\Tests\Larasort\Relations\OneToOne_OneToMany\Fixtures\Models\User;

trait ForOneToOneTrait
{
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
}
