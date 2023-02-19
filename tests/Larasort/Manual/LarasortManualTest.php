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
 * Ici on test essentiellement les méthodes "setSortablesDefaultOrder" et "get".
 * Pour la méthode "get", on test ses keys "order_by" et "order".
 */
class LarasortManualTest extends TestCase
{
    use ForAllTestsTrait;

    public function test_order_by(): void
    {
        $this->verifyInAllTests();

        // Ici on passe à "asc" le order actif en $_GET :

        Request::offsetSet('orderby', 'email');
        Request::offsetSet('order', 'asc');

        $larasortMan = new LarasortManual();
        $larasortMan->setSortables(['created_at', 'email', 'name']);
        $resultLarasortMan = $larasortMan->get();

        $this->assertSame('email', $resultLarasortMan['order_by']);
        $this->assertSame('asc', $resultLarasortMan['order']);

        // Ici on passe à "desc" le order actif en $_GET :

        Request::offsetSet('orderby', 'email');
        Request::offsetSet('order', 'desc');

        $larasortMan = new LarasortManual();
        $larasortMan->setSortables(['created_at', 'email', 'name']);
        $resultLarasortMan = $larasortMan->get();

        $this->assertSame('email', $resultLarasortMan['order_by']);
        $this->assertSame('desc', $resultLarasortMan['order']);
    }

    public function test_order_by_with_table(): void
    {
        $this->verifyInAllTests();

        // Ici on passe à "asc" le order actif en $_GET :

        Request::offsetSet('orderby', 'name');
        Request::offsetSet('order', 'asc');

        $larasortMan = new LarasortManual();
        $larasortMan->setSortables(['created_at', 'email', 'name']);
        $larasortMan->setSortablesToTables(['name' => 'customers.name']); // ICI
        $resultLarasortMan = $larasortMan->get();

        $this->assertSame('customers.name', $resultLarasortMan['order_by']); // ICI
        $this->assertSame('asc', $resultLarasortMan['order']);

        // Ici on passe à "desc" le order actif en $_GET :

        Request::offsetSet('orderby', 'name');
        Request::offsetSet('order', 'desc');

        $larasortMan = new LarasortManual();
        $larasortMan->setSortables(['created_at', 'email', 'name']);
        $larasortMan->setSortablesToTables(['name' => 'customers.name']); // ICI
        $resultLarasortMan = $larasortMan->get();

        $this->assertSame('customers.name', $resultLarasortMan['order_by']); // ICI
        $this->assertSame('desc', $resultLarasortMan['order']);
    }
}
