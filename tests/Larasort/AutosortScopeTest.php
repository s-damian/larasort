<?php

namespace SDamian\Tests\Larasort;

use Illuminate\Support\Facades\Request;
use SDamian\Tests\Larasort\Fixtures\Models\Customer;
use SDamian\Tests\Larasort\Traits\ForAllTestsTrait;
use SDamian\Tests\TestCase;

/**
 * Ici on test le scope "scopeAutosort" du trait AutoSortable.
 */
class AutosortScopeTest extends TestCase
{
    use ForAllTestsTrait;

    public function setUp(): void
    {
        parent::setUp();

        if (getenv('DB_CONNECTION') !== 'testing' || config('database.connections.testing.driver') !== 'sqlite') {
            $this->markTestSkipped('Test requires a Sqlite connection.');
        }

        // Créer la table "customers", et y insert 3 rows :

        $this->artisan('migrate', [
            '--path' => realpath(__DIR__.'/Fixtures/migrations/'),
            '--realpath' => true,
        ]);

        Customer::storeCustomers(3);

        // On a besoin de ces déonnes pour nos tests de cette class
        $this->verifyRowsDataInDb();

        $this->assertSame(3, Customer::count());
    }

    private function verifyRowsDataInDb(): void
    {
        $this->assertDatabaseHas('customers', [
            'email' => 'customer-1@gmail.com',
            'first_name' => 'aaa',
        ]);
        $this->assertDatabaseHas('customers', [
            'email' => 'customer-2@gmail.com',
            'first_name' => 'ccc',
        ]);
        $this->assertDatabaseHas('customers', [
            'email' => 'customer-3@gmail.com',
            'first_name' => 'bbb',
        ]);
    }

    public function test_autosort_scope_without_request(): void
    {
        $this->verifyInAllTests();

        $customers = Customer::select('*')->autosort()->paginate(); // tester "scopeAutosort"
        $this->assertSame(3, $customers->total());

        $customerFirst = $customers->first();
        $customerLast = $customers->last();

        $this->assertSame('customer-1@gmail.com', $customerFirst->email);
        $this->assertSame('customer-3@gmail.com', $customerLast->email);

        // Conclusion :
        // En faisant des asserts sur "email", on constate que la req SQL a bien fait le ORDER BY sur "email" à ASC
        // Ceci est du au fait qu'il n'y a pas de Request ($_GET) actif,
        // donc c'est bien sur "id" (1è colonne dans $sortables) que la req SQL a fait son ORDER BY

        // On fait même test avec la conf "default_order" à "desc" eu lieu d'à "asc" :

        config(['larasort.default_order' => 'desc']);
        $this->assertSame('desc', config('larasort.default_order')); // la conf a bien changée

        $customers = Customer::select('*')->autosort()->paginate(); // tester "scopeAutosort"
        $this->assertSame(3, $customers->total());

        $customerFirst = $customers->first();
        $customerLast = $customers->last();

        $this->assertSame('customer-3@gmail.com', $customerFirst->email);
        $this->assertSame('customer-1@gmail.com', $customerLast->email);

        // Conclusion :
        // Le résultat a bien été inversé. Ceci est du grace au changement de la conf "default_order".
        // En faisant des asserts sur "email", on constate que la req SQL a bien fait le ORDER BY sur "email" à DESC
    }

    public function test_autosort_scope_with_request(): void
    {
        $this->verifyInAllTests();

        Request::offsetSet('orderby', 'first_name'); // ICI
        Request::offsetSet('order', 'asc');

        $customers = Customer::select('*')->autosort()->paginate(); // tester "scopeAutosort"
        $this->assertSame(3, $customers->total());

        $customerFirst = $customers->first();
        $customerLast = $customers->last();

        $this->assertSame('aaa', $customerFirst->first_name);
        $this->assertSame('customer-1@gmail.com', $customerFirst->email);
        $this->assertSame('ccc', $customerLast->first_name);
        $this->assertSame('customer-2@gmail.com', $customerLast->email);

        // Conclusion :
        // En faisant des asserts sur "first_name", on constate que la req SQL a bien fait le ORDER BY sur "first_name" à ASC

        // On fait même test avec la conf "default_order" à "desc" eu lieu d'à "asc" :

        Request::offsetSet('orderby', 'first_name'); // ICI
        Request::offsetSet('order', 'desc');

        $customers = Customer::select('*')->autosort()->paginate(); // tester "scopeAutosort"
        $this->assertSame(3, $customers->total());

        $customerFirst = $customers->first();
        $customerLast = $customers->last();

        $this->assertSame('aaa', $customerLast->first_name);
        $this->assertSame('customer-1@gmail.com', $customerLast->email);
        $this->assertSame('ccc', $customerFirst->first_name);
        $this->assertSame('customer-2@gmail.com', $customerFirst->email);

        // Conclusion :
        // Le résultat a bien été inversé. Ceci est du grace au changement du Request "order" sur "desc".
        // En faisant des asserts sur "first_name", on constate que la req SQL a bien fait le ORDER BY sur "first_name" à DESC
    }
}
