<?php

namespace SDamian\Tests\Larasort;

use Illuminate\Support\Facades\Request;
use SDamian\Larasort\Larasort;
use SDamian\Larasort\LarasortLink;
use SDamian\Tests\Larasort\Traits\ForAllTestsTrait;
use SDamian\Tests\TestCase;

/**
 * Ici on test :
 * - La class LarasortLink
 */
class LarasortLinkTest extends TestCase
{
    use ForAllTestsTrait;

    /**
     * Ici ça passera tout le temps dans le if : (request()->has('orderby') && request()->orderby === $column)
     */
    public function test_larasort_link_if(): void
    {
        $this->verifyInAllTests();

        Request::offsetSet('orderby', 'email');
        Request::offsetSet('order', 'asc');

        // "desc" car $_GET "order" est à "asc"
        $this->assertSame('http://localhost/?orderby=email&order=desc', LarasortLink::getUrl('email'));
        $this->assertSame('href="http://localhost/?orderby=email&order=desc"', LarasortLink::getHref('email'));
        $this->assertSame('<span class="larasort-icon-1"></span>', LarasortLink::getIcon('email'));

        Request::offsetSet('orderby', 'email');
        Request::offsetSet('order', 'desc');

        // "asc" car $_GET "order" est à "desc"
        $this->assertSame('http://localhost/?orderby=email&order=asc', LarasortLink::getUrl('email'));
        $this->assertSame('href="http://localhost/?orderby=email&order=asc"', LarasortLink::getHref('email'));
        $this->assertSame('<span class="larasort-icon-2"></span>', LarasortLink::getIcon('email'));
    }

    public function test_larasort_link_get_link(): void
    {
        Request::offsetSet('orderby', 'email');
        Request::offsetSet('order', 'desc');

        // Et on en profite pour test la méthode "getLink"
        $this->assertSame(
            '<a href="http://localhost/?orderby=email&order=asc">Customer Email<span class="larasort-icon-2"></span></a>',
            LarasortLink::getLink('email', 'Customer Email')
        );

        // Et on en profite pour test la méthode "getLink" (SANS passer de label)
        $this->assertSame(
            '<a href="http://localhost/?orderby=email&order=asc">Email<span class="larasort-icon-2"></span></a>',
            LarasortLink::getLink('email')
        );

        Request::offsetSet('orderby', 'user_name');
        Request::offsetSet('order', 'desc');

        // Et on en profite pour test la méthode "getLink" (SANS passer de label, AVEC une colonne qui contient un underscore)
        $this->assertSame(
            '<a href="http://localhost/?orderby=user_name&order=asc">User name<span class="larasort-icon-2"></span></a>',
            LarasortLink::getLink('user_name')
        );
    }

    /**
     * Ici ça passera tout le temps dans le elseif : (! request()->has('orderby') && $column === self::getDefaultSortableWithoutTable())
     */
    public function test_larasort_link_elseif(): void
    {
        $this->verifyInAllTests();

        Larasort::setDefaultSortable('email');

        // "desc" car la conf "default_order" est à "asc", et car on l'a mis en "setDefaultSortable"
        $this->assertSame('http://localhost/?orderby=email&order=desc', LarasortLink::getUrl('email'));
        $this->assertSame('href="http://localhost/?orderby=email&order=desc"', LarasortLink::getHref('email'));
        $this->assertSame('<span class="larasort-icon-1"></span>', LarasortLink::getIcon('email'));

        // Maintenant on test en changeant la conf :

        config(['larasort.default_order' => 'desc']);
        $this->assertSame('desc', config('larasort.default_order'));

        Larasort::setDefaultSortable('email');

        // passe bien à "asc"
        $this->assertSame('http://localhost/?orderby=email&order=asc', LarasortLink::getUrl('email'));
        $this->assertSame('href="http://localhost/?orderby=email&order=asc"', LarasortLink::getHref('email'));
        $this->assertSame('<span class="larasort-icon-2"></span>', LarasortLink::getIcon('email'));

        Larasort::clearDefaultSortable(); // Pour éviter "conflits" avec les tests suivants.
    }

    /**
     * Ici ça passera tout le temps dans le else
     */
    public function test_larasort_link_else(): void
    {
        $this->verifyInAllTests();

        // "asc" car la conf "default_order" est à "asc"
        $this->assertSame('http://localhost/?orderby=email&order=asc', LarasortLink::getUrl('email'));
        $this->assertSame('href="http://localhost/?orderby=email&order=asc"', LarasortLink::getHref('email'));
        $this->assertSame('<span class="larasort-icon-n-1"></span>', LarasortLink::getIcon('email'));

        config(['larasort.default_order' => 'desc']);
        $this->assertSame('desc', config('larasort.default_order'));

        // "desc" car on vient de passer la conf "default_order" est à "desc"
        $this->assertSame('http://localhost/?orderby=email&order=desc', LarasortLink::getUrl('email'));
        $this->assertSame('href="http://localhost/?orderby=email&order=desc"', LarasortLink::getHref('email'));
        $this->assertSame('<span class="larasort-icon-n-2"></span>', LarasortLink::getIcon('email'));
    }
}
