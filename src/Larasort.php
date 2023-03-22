<?php

declare(strict_types=1);

namespace SDamian\Larasort;

use SDamian\Larasort\Support\Security;
use SDamian\Larasort\Traits\UtilsTrait;

/**
 * Larasort - Main class.
 *
 * @author  Stephen Damian <contact@damian-freelance.fr>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 *
 * @link    https://github.com/s-damian/larasort
 */
final class Larasort
{
    use UtilsTrait;

    private static ?string $defaultSortable = null;

    /**
     * @var array<string, string>
     */
    private static array $sortablesToTables = [];

    /**
     * @var array<mixed>
     */
    private static array $sortablesDefaultOrder = [
        'desc' => [],
        'asc' => [],
    ];

    /*
    |--------------------------------------------------------------------------
    | $defaultSortable
    |--------------------------------------------------------------------------
    */

    /**
     * PS:
     * - At the column passed to "$sortable", it is mandatory to put a column that exists in the table.
     *   We can optionally specify its table as a prefix (in this case, "Larasort::setSortablesDefaultOrder" method will just be ignored for this column).
     */
    final public static function setDefaultSortable(string $defaultSortable): void
    {
        self::$defaultSortable = $defaultSortable;
    }

    final public static function getDefaultSortable(): ?string
    {
        return self::$defaultSortable;
    }

    final public static function clearDefaultSortable(): void
    {
        self::$defaultSortable = null;
    }

    /*
    |--------------------------------------------------------------------------
    | $sortablesToTables
    |--------------------------------------------------------------------------
    */

    /**
     * For column(s), specify its table.
     * This function is especially useful for SQL queries with joins.
     *
     * @param  array<string, string>  $sortablesToTables
     * - In keys: column of the "orderby"
     * - In values: 'table_name.column'
     *   # Example with an "articles" table and its "id" column:
     *   // When we do an order by "id" (in URL: "?orderby=id"), we want SQL to do it on the "id" of the "articles" table.
     *   Larasort::setSortablesToTables([
     *       'id' => 'articles.id',
     *   ]);
     */
    final public static function setSortablesToTables(array $sortablesToTables): void
    {
        self::$sortablesToTables = $sortablesToTables;
    }

    /**
     * @return array<string, string>
     */
    final public static function getSortablesToTables(): array
    {
        if (config('app.env') === 'testing') {
            return [];
        }

        return self::$sortablesToTables;
    }

    final public static function clearSortablesToTables(): void
    {
        self::$sortablesToTables = [];
    }

    /*
    |--------------------------------------------------------------------------
    | $sortablesDefaultOrder
    |--------------------------------------------------------------------------
    */

    /**
     * To possibly specify the columns which are by default at order desc during the 1st click on its link.
     *
     * PS:
     * - To the columns passed to "$sortablesDefaultOrder",
     *   we can optionally specify their table as a prefix (but the table will be ignored thanks to the strpos).
     * - At "$sortablesDefaultOrder", if we put a column that does not exist in the table, it will not crash the program (it will just be ignored).
     *
     * @param  array<mixed>  $sortablesDefaultOrder
     */
    final public static function setSortablesDefaultOrder(array $sortablesDefaultOrder): void
    {
        Security::verifyKeyIsAscOrDescAndValueIsArray($sortablesDefaultOrder);

        self::$sortablesDefaultOrder['desc'] = self::getSortablesDefaultOrderWithoutTable($sortablesDefaultOrder['desc'] ?? []);
        self::$sortablesDefaultOrder['asc'] = self::getSortablesDefaultOrderWithoutTable($sortablesDefaultOrder['asc'] ?? []);
    }

    /**
     * @return array<mixed>
     */
    final public static function getSortablesDefaultOrder(): array
    {
        if (config('app.env') === 'testing') {
            return [
                'desc' => [],
                'asc' => [],
            ];
        }

        return self::$sortablesDefaultOrder;
    }

    final public static function clearSortablesDefaultOrder(): void
    {
        self::$sortablesDefaultOrder = [
            'desc' => [],
            'asc' => [],
        ];
    }
}
