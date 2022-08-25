<?php

namespace SDamian\Tests\Larasort;

use SDamian\Tests\TestCase;
use Illuminate\Support\Facades\Request;
use SDamian\Tests\Larasort\Utils\ForAllTestsTrait;
use SDamian\Tests\Larasort\Fixtures\Models\Address;

/**
 * Ici on fait des tests avec le Model "Address" (qui n' pas de table).
 */
class AutoSortableTraitAddressTest extends TestCase
{
    use ForAllTestsTrait;

    private Address $address;

    public function setUp(): void
    {
        parent::setUp();

        $this->address = new Address();
    }

    /**
     * Ici on test juste la propriété "$sortables" lorsqu'on lui met null en première "colonne".
     * C'est utile si par défaut (lorsque dans l'URL il n'y a pas de ?orderby={colonne}), qu'on ne veut pas mettre ORDER BY à la requête SQL.
     */
    public function testSortablesPropWithNullInFirstPosWithoutRequest(): void
    {
        $this->verifyInAllTests();

        $this->assertTrue($this->address->getSqlOrderBy() === null);
    }

    public function testSortablesPropWithNullInFirstPosWithRequest(): void
    {
        $this->verifyInAllTests();

        // On test avec "name" qu'il a dans "$sortables" :

        Request::offsetSet('orderby', 'name');
        Request::offsetSet('order', 'asc');

        $this->assertSame('addresses.name', $this->address->getSqlOrderBy());
        $this->assertSame('asc', $this->address->getSqlOrder());

        // On test avec "aaazzz" qu'il n'a pas dans "$sortables" :

        Request::offsetSet('orderby', 'aaazzz');
        Request::offsetSet('order', 'asc');

        $this->assertTrue($this->address->getSqlOrderBy() === null);
        // Par défaut sa prend bien sa 1è "colonne" (qui là est null car par défaut on ne veut pas de ORDER BY dans la req SQL).
    }
}
