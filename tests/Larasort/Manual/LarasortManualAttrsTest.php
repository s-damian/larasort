<?php

namespace SDamian\Tests\Larasort\Manual;

use Illuminate\Support\Facades\Request;
use SDamian\Larasort\Manual\LarasortManual;
use SDamian\Tests\Larasort\Traits\ForAllTestsTrait;
use SDamian\Tests\TestCase;

/**
 * Ici on test :
 * - La class LarasortManual
 *
 * Ici on test essentiellement les méthodes "setSortables", "setSortablesDefaultOrder" et "get".
 * Pour la méthode "get()", on test sa key "attrs".
 */
class LarasortManualAttrsTest extends TestCase
{
    use ForAllTestsTrait;

    /*
    |--------------------------------------------------------------------------
    | Tester : $_GET active
    |--------------------------------------------------------------------------
    */

    public function test_attrs_without_get_in_url(): void
    {
        $this->verifyInAllTests();

        $larasortMan = new LarasortManual();
        $larasortMan->setSortables(['created_at', 'email', 'name']);
        $resultLarasortMan = $larasortMan->get();

        $this->assertSame('http://localhost/?orderby=created_at&order=desc', $resultLarasortMan['attrs']['created_at']['url']); // desc
        $this->assertSame('href="http://localhost/?orderby=created_at&order=desc"', $resultLarasortMan['attrs']['created_at']['href']); // desc
        $this->assertSame('<span class="larasort-icon-1"></span>', $resultLarasortMan['attrs']['created_at']['icon']); // larasort-icon-1

        $this->assertSame('http://localhost/?orderby=email&order=asc', $resultLarasortMan['attrs']['email']['url']); // asc
        $this->assertSame('href="http://localhost/?orderby=email&order=asc"', $resultLarasortMan['attrs']['email']['href']); // asc
        $this->assertSame('<span class="larasort-icon-n-1"></span>', $resultLarasortMan['attrs']['email']['icon']); // larasort-icon-n-1

        $this->assertSame('http://localhost/?orderby=name&order=asc', $resultLarasortMan['attrs']['name']['url']); // asc
        $this->assertSame('href="http://localhost/?orderby=name&order=asc"', $resultLarasortMan['attrs']['name']['href']); // asc
        $this->assertSame('<span class="larasort-icon-n-1"></span>', $resultLarasortMan['attrs']['name']['icon']); // larasort-icon-n-1

        // Conclusion :
        // Il n'y a pas de $_GET actif. Donc "created_at" est le order by actif avec son order à "asc" (car cette colonne est en 1è position dans la méthode "setSortables").
        // Donc (contrairement aux 2 autres colonnes) : il a son href (lien cliquable) sur "desc".
    }

    public function test_attrs_with_get_in_url_order_asc(): void
    {
        $this->verifyInAllTests();

        Request::offsetSet('orderby', 'email');
        Request::offsetSet('order', 'asc');

        $larasortMan = new LarasortManual();
        $larasortMan->setSortables(['created_at', 'email', 'name']);
        $resultLarasortMan = $larasortMan->get();

        $this->assertSame('http://localhost/?orderby=created_at&order=asc', $resultLarasortMan['attrs']['created_at']['url']); // asc
        $this->assertSame('href="http://localhost/?orderby=created_at&order=asc"', $resultLarasortMan['attrs']['created_at']['href']); // asc
        $this->assertSame('<span class="larasort-icon-n-1"></span>', $resultLarasortMan['attrs']['created_at']['icon']); // larasort-icon-n-1

        $this->assertSame('http://localhost/?orderby=email&order=desc', $resultLarasortMan['attrs']['email']['url']); // desc
        $this->assertSame('href="http://localhost/?orderby=email&order=desc"', $resultLarasortMan['attrs']['email']['href']); // desc
        $this->assertSame('<span class="larasort-icon-1"></span>', $resultLarasortMan['attrs']['email']['icon']); // larasort-icon-1

        $this->assertSame('http://localhost/?orderby=name&order=asc', $resultLarasortMan['attrs']['name']['url']); // asc
        $this->assertSame('href="http://localhost/?orderby=name&order=asc"', $resultLarasortMan['attrs']['name']['href']); // asc
        $this->assertSame('<span class="larasort-icon-n-1"></span>', $resultLarasortMan['attrs']['name']['icon']); // larasort-icon-n-1

        // Conclusion :
        // Là on a mis $_GET actif à "email" et à "asc". Donc "email" est le order by actif (car il est dans l'URL).
        // Donc (contrairement aux 2 autres colonnes) : il a son href (lien cliquable) sur "desc".
    }

    public function test_attrs_with_get_in_url_order_desc(): void
    {
        $this->verifyInAllTests();

        // Ici on passe le "order" à "desc" :

        Request::offsetSet('orderby', 'email');
        Request::offsetSet('order', 'desc');

        $larasortMan = new LarasortManual();
        $larasortMan->setSortables(['created_at', 'email', 'name']);
        $resultLarasortMan = $larasortMan->get();

        $this->assertSame('http://localhost/?orderby=created_at&order=asc', $resultLarasortMan['attrs']['created_at']['url']); // asc
        $this->assertSame('href="http://localhost/?orderby=created_at&order=asc"', $resultLarasortMan['attrs']['created_at']['href']); // asc
        $this->assertSame('<span class="larasort-icon-n-1"></span>', $resultLarasortMan['attrs']['created_at']['icon']); // larasort-icon-n-1

        $this->assertSame('http://localhost/?orderby=email&order=asc', $resultLarasortMan['attrs']['email']['url']); // ICI asc
        $this->assertSame('href="http://localhost/?orderby=email&order=asc"', $resultLarasortMan['attrs']['email']['href']); // ICI asc
        $this->assertSame('<span class="larasort-icon-2"></span>', $resultLarasortMan['attrs']['email']['icon']); // ICI larasort-icon-2

        $this->assertSame('http://localhost/?orderby=name&order=asc', $resultLarasortMan['attrs']['name']['url']); // asc
        $this->assertSame('href="http://localhost/?orderby=name&order=asc"', $resultLarasortMan['attrs']['name']['href']); // asc
        $this->assertSame('<span class="larasort-icon-n-1"></span>', $resultLarasortMan['attrs']['name']['icon']); // larasort-icon-n-1

        // Conclusion :
        // Après avoir passé à "email" son "order" à "desc", son href est maintenant "asc".
    }

    /*
    |--------------------------------------------------------------------------
    | Tester : Mettre par défaut à "desc" pour certaines colonnes
    |--------------------------------------------------------------------------
    */

    public function test_set_sortables_default_order_without_get_in_url(): void
    {
        $this->verifyInAllTests();

        $larasortMan = new LarasortManual();
        $larasortMan->setSortables(['created_at', 'email', 'name']);
        $larasortMan->setSortablesDefaultOrder([
            'desc' => ['created_at'], // ICI
        ]);
        $resultLarasortMan = $larasortMan->get();

        $this->assertSame('http://localhost/?orderby=created_at&order=asc', $resultLarasortMan['attrs']['created_at']['url']); // ICI
        $this->assertSame('href="http://localhost/?orderby=created_at&order=asc"', $resultLarasortMan['attrs']['created_at']['href']); // ICI
        $this->assertSame('<span class="larasort-icon-2"></span>', $resultLarasortMan['attrs']['created_at']['icon']); // ICI

        $this->assertSame('http://localhost/?orderby=email&order=asc', $resultLarasortMan['attrs']['email']['url']); // asc
        $this->assertSame('href="http://localhost/?orderby=email&order=asc"', $resultLarasortMan['attrs']['email']['href']); // asc
        $this->assertSame('<span class="larasort-icon-n-1"></span>', $resultLarasortMan['attrs']['email']['icon']); // larasort-icon-n-1

        $this->assertSame('http://localhost/?orderby=name&order=asc', $resultLarasortMan['attrs']['name']['url']); // asc
        $this->assertSame('href="http://localhost/?orderby=name&order=asc"', $resultLarasortMan['attrs']['name']['href']); // asc
        $this->assertSame('<span class="larasort-icon-n-1"></span>', $resultLarasortMan['attrs']['name']['icon']); // larasort-icon-n-1

        // Conclusion :
        // Il n'y a pas de $_GET actif. Donc "created_at" est le order by actif (cer est en 1è position dans la méthode "setSortables").
        // Mais là il a son order à "desc" (car on l'a mis dans la méthode "setSortablesDefaultOrder").
    }

    public function test_set_sortables_default_order_with_get_in_url_order_asc(): void
    {
        $this->verifyInAllTests();

        Request::offsetSet('orderby', 'email');
        Request::offsetSet('order', 'asc');

        $larasortMan = new LarasortManual();
        $larasortMan->setSortables(['created_at', 'email', 'name']);
        $larasortMan->setSortablesDefaultOrder([
            'desc' => ['created_at'], // ICI
        ]);
        $resultLarasortMan = $larasortMan->get();

        $this->assertSame('http://localhost/?orderby=created_at&order=desc', $resultLarasortMan['attrs']['created_at']['url']); // ICI
        $this->assertSame('href="http://localhost/?orderby=created_at&order=desc"', $resultLarasortMan['attrs']['created_at']['href']); // ICI
        $this->assertSame('<span class="larasort-icon-n-2"></span>', $resultLarasortMan['attrs']['created_at']['icon']); // ICI

        $this->assertSame('http://localhost/?orderby=email&order=desc', $resultLarasortMan['attrs']['email']['url']); // ICI
        $this->assertSame('href="http://localhost/?orderby=email&order=desc"', $resultLarasortMan['attrs']['email']['href']); // ICI
        $this->assertSame('<span class="larasort-icon-1"></span>', $resultLarasortMan['attrs']['email']['icon']); // ICI

        $this->assertSame('http://localhost/?orderby=name&order=asc', $resultLarasortMan['attrs']['name']['url']); // asc
        $this->assertSame('href="http://localhost/?orderby=name&order=asc"', $resultLarasortMan['attrs']['name']['href']); // asc
        $this->assertSame('<span class="larasort-icon-n-1"></span>', $resultLarasortMan['attrs']['name']['icon']); // larasort-icon-n-1

        // Conclusion :
        // Là on a mis $_GET actif à "email" et à "asc". Donc "email" est le order by actif (car il est dans l'URL).
        // Et vu qu'on a mis "created_at" dans la méthode "setSortablesDefaultOrder" : "created_at" a son href (lien cliquable) sur "desc" au lieu de "asc".
    }

    public function test_set_sortables_default_order_with_get_in_url_order_desc(): void
    {
        $this->verifyInAllTests();

        Request::offsetSet('orderby', 'email');
        Request::offsetSet('order', 'desc');

        $larasortMan = new LarasortManual();
        $larasortMan->setSortables(['created_at', 'email', 'name']);
        $larasortMan->setSortablesDefaultOrder([
            'desc' => ['created_at'], // ICI
        ]);
        $resultLarasortMan = $larasortMan->get();

        $this->assertSame('http://localhost/?orderby=created_at&order=desc', $resultLarasortMan['attrs']['created_at']['url']); // ICI
        $this->assertSame('href="http://localhost/?orderby=created_at&order=desc"', $resultLarasortMan['attrs']['created_at']['href']); // ICI
        $this->assertSame('<span class="larasort-icon-n-2"></span>', $resultLarasortMan['attrs']['created_at']['icon']); // ICI

        $this->assertSame('http://localhost/?orderby=email&order=asc', $resultLarasortMan['attrs']['email']['url']); // ICI
        $this->assertSame('href="http://localhost/?orderby=email&order=asc"', $resultLarasortMan['attrs']['email']['href']); // ICI
        $this->assertSame('<span class="larasort-icon-2"></span>', $resultLarasortMan['attrs']['email']['icon']); // ICI

        $this->assertSame('http://localhost/?orderby=name&order=asc', $resultLarasortMan['attrs']['name']['url']); // asc
        $this->assertSame('href="http://localhost/?orderby=name&order=asc"', $resultLarasortMan['attrs']['name']['href']); // asc
        $this->assertSame('<span class="larasort-icon-n-1"></span>', $resultLarasortMan['attrs']['name']['icon']); // larasort-icon-n-1

        // Conclusion :
        // Là on a mis $_GET actif à "email" et à "desc". Donc "email" est le order by actif (car il est dans l'URL).
        // Et ve qu'on a mis "created_at" dans la méthode "setSortablesDefaultOrder" : "created_at" a son href (lien cliquable) toujours sur "desc"
        // (normal car on lui a rien changé par rapport à la précédante méthode de test).
    }
}
