<?php

namespace SDamian\Tests\Larasort\Fixtures\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use SDamian\Larasort\AutoSortable;
use SDamian\Tests\Larasort\Fixtures\Factories\CustomerFactory;

class Customer extends Model
{
    use AutoSortable; // For Larasort

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * For Larasort
     * The attributes that are sortable.
     *
     * @var array<null|string>
     */
    private array $sortables = [
        'id',
        'email',
        'first_name',
        'last_name',
        'price',
    ];

    /**
     * The attributes that are sortable without table in prefix.
     *
     * @var array<string>
     */
    private array $sortablesAs = [
        'article_title',
    ];

    /**
     * For Larasort
     * The sortable attributes to which their table is specified.
     *
     * PS :
     * 'id' => 'customers.id', :
     * ça ne sert à rien (mais on test qu'il n'y est pas d'incidence).
     * ça ne sert à rien car Larasort par défaut mett déjà en préfix de la table où le trait AutoSortable est inclut
     *
     * @var array<string, string>
     */
    private array $sortablesToTables = [
        'id' => 'customers.id',
        'price' => 'orders.id',
    ];

    public static function storeCustomers(int $nb): void
    {
        for ($i = 1; $i <= $nb; $i++) {
            switch ($i) {
                case 1:
                    $first_name = 'aaa';

                    break;
                case 2:
                    $first_name = 'ccc';

                    break;
                case 3:
                    $first_name = 'bbb';

                    break;
            }

            CustomerFactory::new()->create([
                'email' => 'customer-'.$i.'@gmail.com',
                'first_name' => $first_name ?? Str::random(20), // avec le switch, astuce : servira à tester le ORDER BY avec $_GET actif
            ]);
        }
    }
}
