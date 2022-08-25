<?php

namespace SDamian\Tests\Larasort;

use SDamian\Tests\TestCase;
use SDamian\Larasort\Larasort;
use SDamian\Larasort\LarasortLink;
use Illuminate\Support\Facades\Request;
use SDamian\Tests\Larasort\Utils\ForAllTestsTrait;

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
    public function testLarasortLinkIf(): void
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

        // Et on en profite pour test la méthode "getLink"
        $this->assertSame(
            '<a href="http://localhost/?orderby=email&order=asc">Customer Email<span class="larasort-icon-2"></span></a>',
            LarasortLink::getLink('email', 'Customer Email')
        );
    }

    /**
     * Ici ça passera tout le temps dans le elseif : (! request()->has('orderby') && $column === self::getDefaultSortableWithoutTable())
     */
    public function testLarasortLinkElseif(): void
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

        Larasort::setDefaultSortable('NULL'); // Pour éviter "conflits" avec les tests suivants.
    }

    /**
     * Ici ça passera tout le temps dans le else
     */
    public function testLarasortLinkElse(): void
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
