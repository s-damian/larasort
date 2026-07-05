<?php

declare(strict_types=1);

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

    /*
    |--------------------------------------------------------------------------
    | Tests des méthodes "V2"
    |--------------------------------------------------------------------------
    */

    /**
     * Test de "getUrlV2" - l'URL générée dépend uniquement du paramètre $ascOrDesc.
     */
    public function test_larasort_link_v2_get_url(): void
    {
        $this->verifyInAllTests();

        // Sans rien dans la request, l'URL dépend juste de $ascOrDesc.
        $this->assertSame('http://localhost/?orderby=email&order=asc', LarasortLink::getUrlV2('email', 'asc'));
        $this->assertSame('http://localhost/?orderby=email&order=desc', LarasortLink::getUrlV2('email', 'desc'));

        // Toute valeur autre que "asc" doit retomber sur "desc".
        $this->assertSame('http://localhost/?orderby=email&order=desc', LarasortLink::getUrlV2('email', 'autre-valeur'));

        Request::offsetSet('orderby', 'email');
        Request::offsetSet('order', 'desc');

        // Contrairement à "getUrl", l'ordre ne s'inverse PAS selon la request : il reste celui demandé.
        $this->assertSame('http://localhost/?orderby=email&order=asc', LarasortLink::getUrlV2('email', 'asc'));
        $this->assertSame('http://localhost/?orderby=email&order=desc', LarasortLink::getUrlV2('email', 'desc'));
    }

    /**
     * Test de "getHrefV2".
     */
    public function test_larasort_link_v2_get_href(): void
    {
        $this->verifyInAllTests();

        $this->assertSame('href="http://localhost/?orderby=email&order=asc"', LarasortLink::getHrefV2('email', 'asc'));
        $this->assertSame('href="http://localhost/?orderby=email&order=desc"', LarasortLink::getHrefV2('email', 'desc'));

        // Avec une colonne qui contient un underscore.
        $this->assertSame('href="http://localhost/?orderby=user_name&order=asc"', LarasortLink::getHrefV2('user_name', 'asc'));
        $this->assertSame('href="http://localhost/?orderby=user_name&order=desc"', LarasortLink::getHrefV2('user_name', 'desc'));
    }

    /**
     * Test de "getIconV2" - SANS "order" dans la request : aucune icône n'est active.
     */
    public function test_larasort_link_v2_get_icon_without_order_in_request(): void
    {
        $this->verifyInAllTests();

        $this->assertSame('<span class="larasort-icon-1_v2"></span>', LarasortLink::getIconV2('email', 'asc'));
        $this->assertSame('<span class="larasort-icon-2_v2"></span>', LarasortLink::getIconV2('email', 'desc'));
    }

    /**
     * Test de "getIconV2" - AVEC "orderby" et "order" dans la request :
     * l'icône correspondante reçoit " v2-active" uniquement si la colonne correspond aussi.
     */
    public function test_larasort_link_v2_get_icon_with_order_in_request(): void
    {
        $this->verifyInAllTests();

        Request::offsetSet('orderby', 'email');
        Request::offsetSet('order', 'asc');

        // "orderby" est à "email" et "order" est à "asc" -> seule l'icône "asc" de "email" est active.
        $this->assertSame('<span class="larasort-icon-1_v2 v2-active"></span>', LarasortLink::getIconV2('email', 'asc'));
        $this->assertSame('<span class="larasort-icon-2_v2"></span>', LarasortLink::getIconV2('email', 'desc'));

        // Pour une AUTRE colonne, aucune icône n'est active (même si "order" est à "asc").
        $this->assertSame('<span class="larasort-icon-1_v2"></span>', LarasortLink::getIconV2('user_name', 'asc'));
        $this->assertSame('<span class="larasort-icon-2_v2"></span>', LarasortLink::getIconV2('user_name', 'desc'));

        Request::offsetSet('order', 'desc');

        // "order" est à "desc" -> seule l'icône "desc" de "email" est active.
        $this->assertSame('<span class="larasort-icon-1_v2"></span>', LarasortLink::getIconV2('email', 'asc'));
        $this->assertSame('<span class="larasort-icon-2_v2 v2-active"></span>', LarasortLink::getIconV2('email', 'desc'));

        // On vérifie aussi que la casse de "order" est bien ignorée (mb_strtolower).
        Request::offsetSet('order', 'ASC');
        $this->assertSame('<span class="larasort-icon-1_v2 v2-active"></span>', LarasortLink::getIconV2('email', 'asc'));
    }

    /**
     * Test de "getHrefV2Class" - retourne la class CSS du lien selon $ascOrDesc.
     */
    public function test_larasort_link_v2_get_href_class(): void
    {
        $this->verifyInAllTests();

        $this->assertSame('class="href-larasort-1_v2"', LarasortLink::getHrefV2Class('asc'));
        $this->assertSame('class="href-larasort-2_v2"', LarasortLink::getHrefV2Class('desc'));

        // Toute valeur autre que "asc" doit retomber sur la class "desc".
        $this->assertSame('class="href-larasort-2_v2"', LarasortLink::getHrefV2Class('autre-valeur'));
    }

    /**
     * Test de "getLinkV2" - le label est suivi d'un span "out-href_v2" qui contient les 2 liens (asc puis desc).
     */
    public function test_larasort_link_v2_get_link(): void
    {
        $this->verifyInAllTests();

        Request::offsetSet('orderby', 'email');
        Request::offsetSet('order', 'desc');

        // Et on en profite pour test la méthode "getLinkV2" (AVEC label). "order" est à "desc" -> l'icône "desc" est active.
        $this->assertSame(
            'Customer Email'
                .'<span class="out-href_v2">'
                .'<a class="href-larasort-1_v2" href="http://localhost/?orderby=email&order=asc"><span class="larasort-icon-1_v2"></span></a>'
                .'<a class="href-larasort-2_v2" href="http://localhost/?orderby=email&order=desc"><span class="larasort-icon-2_v2 v2-active"></span></a>'
                .'</span>',
            LarasortLink::getLinkV2('email', 'Customer Email')
        );

        // Et on en profite pour test la méthode "getLinkV2" (SANS passer de label).
        $this->assertSame(
            'Email'
                .'<span class="out-href_v2">'
                .'<a class="href-larasort-1_v2" href="http://localhost/?orderby=email&order=asc"><span class="larasort-icon-1_v2"></span></a>'
                .'<a class="href-larasort-2_v2" href="http://localhost/?orderby=email&order=desc"><span class="larasort-icon-2_v2 v2-active"></span></a>'
                .'</span>',
            LarasortLink::getLinkV2('email')
        );

        Request::offsetSet('orderby', 'user_name');
        Request::offsetSet('order', 'asc');

        // (SANS passer de label, AVEC une colonne qui contient un underscore). "order" est à "asc" -> l'icône "asc" est active.
        $this->assertSame(
            'User name'
                .'<span class="out-href_v2">'
                .'<a class="href-larasort-1_v2" href="http://localhost/?orderby=user_name&order=asc"><span class="larasort-icon-1_v2 v2-active"></span></a>'
                .'<a class="href-larasort-2_v2" href="http://localhost/?orderby=user_name&order=desc"><span class="larasort-icon-2_v2"></span></a>'
                .'</span>',
            LarasortLink::getLinkV2('user_name')
        );
    }
}
