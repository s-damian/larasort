<?php

declare(strict_types=1);

namespace SDamian\Larasort\Manual;

use SDamian\Larasort\Traits\UtilsTrait;

/**
 * Larasort Manual - To do sorting without Eloquent ORM.
 *
 * @author  Stephen Damian <contact@damian-freelance.fr>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 *
 * @link    https://github.com/s-damian/larasort
 */
class LarasortManual
{
    use UtilsTrait;

    /**
     * @var array<null|string>
     */
    private array $sortables = [];

    /**
     * @var array<string, string>
     */
    private array $sortablesToTables = [];

    /**
     * @var array<mixed>
     */
    private array $sortablesDefaultOrder = [
        'desc' => [],
        'asc' => [],
    ];

    /**
     * To specify sortable columns.
     * The default column must be put in the 1st position.
     *
     * PS:
     * - To the columns passed to "$sortables", you must not specify their table as a prefix.
     *
     * @param  array<null|string>  $sortables
     */
    final public function setSortables(array $sortables): void
    {
        $this->sortables = $sortables;
    }

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
    final public function setSortablesToTables(array $sortablesToTables): void
    {
        $this->sortablesToTables = $sortablesToTables;
    }

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
    final public function setSortablesDefaultOrder(array $sortablesDefaultOrder): void
    {
        $this->sortablesDefaultOrder['desc'] = self::getSortablesDefaultOrderWithoutTable($sortablesDefaultOrder['desc'] ?? []);
        $this->sortablesDefaultOrder['asc'] = self::getSortablesDefaultOrderWithoutTable($sortablesDefaultOrder['asc'] ?? []);
    }

    /**
     * @return array<string, mixed>
     */
    final public function get(): array
    {
        $order = $this->getSqlOrder();

        // Will be returned in the view (in the thead and tfoot of the array).
        $attrs = [];

        // For each column allowed to be in order by, assign it its CSS class and its clickable href.
        foreach ($this->sortables as $column) {
            // if : if this column is active column in $_GET, or if no active in $_GET but it is default column.
            if ($this->getOrderByWithoutTable() === $column) {
                if ($order === 'asc') {
                    $url = request()->fullUrlWithQuery(['orderby' => $column, 'order' => 'desc']);
                    $attrs[$column]['url'] = $url;
                    $attrs[$column]['href'] = $this->getHref($url);
                    $attrs[$column]['icon'] = $this->getIcon('larasort-icon-1');
                } else {
                    $url = request()->fullUrlWithQuery(['orderby' => $column, 'order' => 'asc']);
                    $attrs[$column]['url'] = $url;
                    $attrs[$column]['href'] = $this->getHref($url);
                    $attrs[$column]['icon'] = $this->getIcon('larasort-icon-2');
                }
            } else {
                if (config('larasort.default_order') === 'desc') {
                    if (in_array($column, $this->sortablesDefaultOrder['asc'])) {
                        $url = request()->fullUrlWithQuery(['orderby' => $column, 'order' => 'asc']);
                        $attrs[$column]['url'] = $url;
                        $attrs[$column]['href'] = $this->getHref($url);
                        $attrs[$column]['icon'] = $this->getIcon('larasort-icon-n-1');
                    } else {
                        $url = request()->fullUrlWithQuery(['orderby' => $column, 'order' => 'desc']);
                        $attrs[$column]['url'] = $url;
                        $attrs[$column]['href'] = $this->getHref($url);
                        $attrs[$column]['icon'] = $this->getIcon('larasort-icon-n-2');
                    }
                } else {
                    if (in_array($column, $this->sortablesDefaultOrder['desc'])) {
                        $url = request()->fullUrlWithQuery(['orderby' => $column, 'order' => 'desc']);
                        $attrs[$column]['url'] = $url;
                        $attrs[$column]['href'] = $this->getHref($url);
                        $attrs[$column]['icon'] = $this->getIcon('larasort-icon-n-2');
                    } else {
                        $url = request()->fullUrlWithQuery(['orderby' => $column, 'order' => 'asc']);
                        $attrs[$column]['url'] = $url;
                        $attrs[$column]['href'] = $this->getHref($url);
                        $attrs[$column]['icon'] = $this->getIcon('larasort-icon-n-1');
                    }
                }
            }
        }

        return [
            'order_by' => $this->getSqlOrderBy(),
            'order' => $order,
            'attrs' => $attrs,
        ];
    }

    /**
     * Returns the order by for the SQL.
     */
    private function getSqlOrderBy(): ?string
    {
        if (request()->has('orderby') && in_array(request()->orderby, $this->sortables)) {
            $orderBy = request()->orderby;
        } else {
            $orderBy = $this->sortables[0];
        }

        // if : if column (key) is assigned to a table.
        if (array_key_exists($orderBy, $this->sortablesToTables)) {
            return $this->sortablesToTables[$orderBy];
        }

        return $orderBy;
    }

    /**
     * Returns the order by without the table prefix of the column.
     */
    private function getOrderByWithoutTable(): ?string
    {
        $orderBy = $this->getSqlOrderBy();

        if (strpos((string) $orderBy, '.') !== false) {
            $ex = explode('.', (string) $orderBy);
            $orderByWithoutTable = $ex[1];

            return $orderByWithoutTable;
        }

        return $orderBy;
    }

    /**
     * Returns the direction of the order by ("asc" or "desc").
     */
    private function getSqlOrder(): string
    {
        if (request()->has('order')) {
            return strtolower(request()->order) === 'desc' ? 'desc' : 'asc';
        }

        $orderBy = $this->getSqlOrderBy();

        // We manage: so that in "setSortablesDefaultOrder" method whether or not it is possible to put the table in prefix.
        if (strpos((string) $orderBy, '.') !== false) {
            if (in_array($this->getOrderByWithoutTable(), $this->sortablesDefaultOrder['desc'])) {
                return 'desc';
            } elseif (in_array($this->getOrderByWithoutTable(), $this->sortablesDefaultOrder['asc'])) {
                return 'asc';
            }
        }

        if (config('larasort.default_order') === 'desc') {
            return in_array($orderBy, $this->sortablesDefaultOrder['asc']) ? 'asc' : 'desc';
        }

        return in_array($orderBy, $this->sortablesDefaultOrder['desc']) ? 'desc' : 'asc';
    }

    private function getHref(string $url): string
    {
        return 'href="'.$url.'"';
    }

    private function getIcon(string $class): string
    {
        return '<span class="'.$class.'"></span>';
    }
}
