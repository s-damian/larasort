<?php

namespace SDamian\Tests\Larasort;

use Illuminate\Support\Facades\Request;
use SDamian\Larasort\Larasort;
use SDamian\Tests\Larasort\Fixtures\Models\Address;
use SDamian\Tests\Larasort\Fixtures\Models\Customer;
use SDamian\Tests\Larasort\Traits\ForAllTestsTrait;
use SDamian\Tests\TestCase;

/**
 * Ici on test :
 * - Le trait AutoSortable
 * - La class Larasort
 */
class AutoSortableTraitTest extends TestCase
{
    use ForAllTestsTrait;

    private Customer $customer;

    public function setUp(): void
    {
        parent::setUp();

        $this->customer = new Customer();
    }

    /*
    |--------------------------------------------------------------------------
    | Teser : toutes les méthodes du trait AutoSortable
    |--------------------------------------------------------------------------
    */

    /**
     * On test "Case 1" de la méthode "getSqlOrder" du trait "AutoSortable". (this case = "Request" has "order").
     */
    public function test_sql_order_case_1(): void
    {
        $this->verifyInAllTests();

        Request::offsetSet('orderby', 'email');
        Request::offsetSet('order', 'asc');

        // par défaut en préfix du la colonne, le ORDER BY met bien la table où le trait AutoSortable est inclut
        $this->assertSame('customers.email', $this->customer->getSqlOrderBy());
        $this->assertSame('asc', $this->customer->getSqlOrder());

        // On passe à "desc" :

        Request::offsetSet('orderby', 'email');
        Request::offsetSet('order', 'desc');

        $this->assertSame('customers.email', $this->customer->getSqlOrderBy());
        $this->assertSame('desc', $this->customer->getSqlOrder());

        // On test "$sortablesToTables"

        Request::offsetSet('orderby', 'id'); // ICI
        Request::offsetSet('order', 'asc');

        $this->assertSame('customers.id', $this->customer->getSqlOrderBy()); // 'id' => 'customers.id', dans $sortablesToTables = pas d'incidence
        $this->assertSame('asc', $this->customer->getSqlOrder());
    }

    /**
     * On test "Case 3" de la méthode "getSqlOrder" du trait "AutoSortable". (this case = "Request" n'a pas "order").
     */
    public function test_sql_order_case_3(): void
    {
        $this->verifyInAllTests();

        // On ne met pas de Request "orderby", donc ça sera par défaut sur la colonne "id".

        $this->assertSame('customers.id', $this->customer->getSqlOrderBy()); // 'id' => 'customers.id', dans $sortablesToTables = pas d'incidence
        $this->assertSame('asc', $this->customer->getSqlOrder());

        // On passe à "desc" la conf "default_order" :

        config(['larasort.default_order' => 'desc']);
        $this->assertSame('desc', config('larasort.default_order')); // la conf a bien changée

        $this->assertSame('customers.id', $this->customer->getSqlOrderBy());
        $this->assertSame('desc', $this->customer->getSqlOrder());
    }

    public function test_sortablesG_getters_for_customer_model(): void
    {
        // La prop "$sortables" du Model "Customer" a bien fonctionnée.
        $this->assertSame($this->customer->getSortables(), [
            'id',
            'email',
            'first_name',
            'last_name',
            'price',
        ]);

        // La prop "$sortablesAs" du Model "Customer" a bien fonctionnée.
        $this->assertSame($this->customer->getSortablesAs(), [
            'article_title',
        ]);

        // La prop "$sortablesRelated" du Model "Customer" a bien fonctionnée.
        $this->assertSame($this->customer->getSortablesRelated(), []);
    }

    /*
    |--------------------------------------------------------------------------
    | Teser : D'autres comportements
    |--------------------------------------------------------------------------
    */

    /**
     * Teser l'aliasing
     */
    public function test_sortables_as_property(): void
    {
        $this->verifyInAllTests();

        Request::offsetSet('orderby', 'article_title');
        Request::offsetSet('order', 'asc');

        // par défaut en préfix du la colonne, le ORDER BY met bien la table où le trait AutoSortable est inclut
        $this->assertSame('article_title', $this->customer->getSqlOrderBy()); // c'est ok, car pas de table en prefix
        $this->assertSame('asc', $this->customer->getSqlOrder());
    }

    /*
    |--------------------------------------------------------------------------
    | Teser : toutes les méthodes de la class Larasort
    |--------------------------------------------------------------------------
    */

    /**
     * A la class Larasort, tester ces méthodes : "setSortablesDefaultOrder" et "getSortablesDefaultOrder".
     *
     * On test "Case 1" de la méthode "getSqlOrder" du trait "AutoSortable".
     * (this case = "Request" n'a pas "order", et colonne de "orderby" est dans "Larasort::setSortablesDefaultOrder").
     */
    public function test_sql_order_case_2(): void
    {
        $this->verifyInAllTests();

        $this->assertSame(['desc' => [], 'asc' => []], Larasort::getSortablesDefaultOrder());

        Larasort::setSortablesDefaultOrder([
            'desc' => ['id'],
        ]);

        $this->assertSame(['desc' => ['id'], 'asc' => []], Larasort::getSortablesDefaultOrder());

        $this->assertSame('customers.id', $this->customer->getSqlOrderBy());
        $this->assertSame('desc', $this->customer->getSqlOrder()); // ICI

        Larasort::clearSortablesDefaultOrder(); // on "reset" (pour éviter éventuels conflits avec les prochains tests)
        // on test que la méthode "clearSortablesDefaultOrder" fonctionne bien
        $this->assertSame(['desc' => [], 'asc' => []], Larasort::getSortablesDefaultOrder());
    }

    /**
     * A la class Larasort, tester ces méthodes : "setDefaultSortable" et "getDefaultSortable" et "clearDefaultSortable".
     */
    public function test_default_sortable(): void
    {
        $this->verifyInAllTests();

        $this->assertSame('customers.id', $this->customer->getSqlOrderBy()); // car est en 1è position dans "$sortablesToTables" du Model Customer
        $this->assertSame('asc', $this->customer->getSqlOrder());

        // On surcharge la colonne par défaut :

        Larasort::setDefaultSortable('email');
        $this->assertSame('email', Larasort::getDefaultSortable());

        $this->assertSame('customers.email', $this->customer->getSqlOrderBy()); // ICI
        $this->assertSame('asc', $this->customer->getSqlOrder());

        // On surcharge la colonne par défaut (cette fois, avec une colonne qui est dans $sortablesToTables) :

        Larasort::setDefaultSortable('price');
        $this->assertSame('price', Larasort::getDefaultSortable());

        $this->assertSame('orders.id', $this->customer->getSqlOrderBy()); // ICI
        $this->assertSame('asc', $this->customer->getSqlOrder());

        Larasort::clearDefaultSortable(); // on "reset" (pour éviter éventuels conflits avec les prochains tests)
        // on test que la méthode "clearDefaultSortable" fonctionne bien
        $this->assertSame(null, Larasort::getDefaultSortable());
    }

    /**
     * A la class Larasort, tester ces méthodes : "setSortablesToTables" et "getSortablesToTables".
     */
    public function test_sortables_to_tables(): void
    {
        $this->verifyInAllTests();

        $this->assertSame('customers.id', $this->customer->getSqlOrderBy()); // est précisé dans "$sortablesToTables" du Model Customer
        $this->assertSame('asc', $this->customer->getSqlOrder());

        // On surcharge "$sortablesToTables" :

        $this->assertSame([], Larasort::getSortablesToTables());

        Larasort::setSortablesToTables(['id' => 'orders.id']); // Here.
        $this->assertSame(['id' => 'orders.id'], Larasort::getSortablesToTables());

        $this->assertSame('orders.id', $this->customer->getSqlOrderBy()); // est précisé dans "$sortablesToTables" du Model Customer
        $this->assertSame('asc', $this->customer->getSqlOrder());

        Larasort::clearSortablesToTables(); // on "reset" (pour éviter éventuels conflits avec les prochains tests)
        // on test que la méthode "clearSortablesToTables" fonctionne bien
        $this->assertSame([], Larasort::getSortablesToTables());
    }

    /*
    |--------------------------------------------------------------------------
    | Teser : on test des sécurités
    |--------------------------------------------------------------------------
    */

    public function test_wrong_column_in_order_by_url(): void
    {
        $this->verifyInAllTests();

        $this->assertSame('customers.id', $this->customer->getSqlOrderBy()); // c'est bien la colonne par défaut

        Request::offsetSet('orderby', 'email'); // on met une autre colonne

        $this->assertSame('customers.email', $this->customer->getSqlOrderBy()); // ça passe bien à "email"

        Request::offsetSet('orderby', 'aaaaazzzzz'); // on met n'inporte quoi

        $this->assertSame('customers.id', $this->customer->getSqlOrderBy()); // c'est bien la colonne par défaut qui est mis (par sécurité)
    }

    /*
    |--------------------------------------------------------------------------
    | Teser : faire des test avec le Model "Address" (qui a null en 1è "colonne" de sa propriété "$sortables").
    |--------------------------------------------------------------------------
    */

    /**
     * Ici on test juste la propriété "$sortables" lorsqu'on lui met null en première "colonne".
     * C'est utile si par défaut (lorsque dans l'URL il n'y a pas de $_GET actif) qu'on ne veut pas mettre de ORDER BY à la requête SQL.
     */
    public function test_sortables_prop_with_null_in_first_pos_without_request(): void
    {
        $this->verifyInAllTests();

        $address = new Address();

        $this->assertTrue($address->getSqlOrderBy() === null);
    }

    public function test_sortables_prop_with_nullIn_first_pos_with_request(): void
    {
        $this->verifyInAllTests();

        $address = new Address();

        // On test avec "name" qu'il a bien dans "$sortables" :

        Request::offsetSet('orderby', 'name');
        Request::offsetSet('order', 'asc');

        $this->assertSame('addresses.name', $address->getSqlOrderBy());
        $this->assertSame('asc', $address->getSqlOrder());

        // On test avec "aaazzz" qu'il n'a pas dans "$sortables" :

        Request::offsetSet('orderby', 'aaazzz');
        Request::offsetSet('order', 'asc');

        $this->assertTrue(is_null($address->getSqlOrderBy()));
        // Par défaut sa prend bien sa 1è "colonne" (qui là est null car par défaut on ne veut pas de ORDER BY dans la requête SQL).
    }

    public function test_sortables_getters_for_address_model(): void
    {
        $address = new Address();

        // La prop "$sortables" du Model "Address" a bien fonctionnée.
        $this->assertSame($address->getSortables(), [
            null,
            'id',
            'name',
        ]);

        // La prop "$sortablesAs" du Model "Address" a bien fonctionnée.
        $this->assertSame($address->getSortablesAs(), []);

        // La prop "$sortablesRelated" du Model "Address" a bien fonctionnée.
        $this->assertSame($address->getSortablesRelated(), []);
    }
}
