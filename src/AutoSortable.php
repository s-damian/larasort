<?php

declare(strict_types=1);

namespace SDamian\Larasort;

use Illuminate\Database\Eloquent\Builder as BuilderE;
use Illuminate\Database\Query\Builder;
use SDamian\Larasort\Exception\LarasortException;
use SDamian\Larasort\Relations\OrderByRelated;
use SDamian\Larasort\Relations\Related;
use SDamian\Larasort\Support\Security;

/**
 * Larasort - To do sorting with Eloquent ORM.
 * This Trait is useful for automating the "ORDER BY `{column}` {direction}" of SQL queries with Eloquent ORM.
 *
 * # In Models that use this Trait, you can use 2 properties:
 * - $sortables property (must be an array):
 *   This property must be present.
 *   In values, it must be given the columns of the Model table.
 *   To the columns passed to "$sortables", you must not specify their table as a prefix.
 * - $sortablesToTables property (must be an array):
 *   This property is optional.
 *   Instead of this property, you can use "Larasort::setSortablesToTables" method.
 *   PS: If "$sortablesToTables" property and "Larasort::setSortablesToTables" method are used at the same time for the same column,
 *       "Larasort::setSortablesToTables" method will override "$sortablesToTables" property.
 *
 * @author  Stephen Damian <contact@damian-freelance.fr>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 *
 * @link    https://github.com/s-damian/larasort
 */
trait AutoSortable
{
    /*
    |--------------------------------------------------------------------------
    | Scopes:
    |--------------------------------------------------------------------------
    */

    /**
     * @param  array<mixed>  $options
     */
    final public function scopeAutosortWith(Builder|BuilderE $query, string $relation, array $options = []): Builder|BuilderE
    {
        Security::verifyScopeAutosortOptions($options);

        $related = new Related($this, $query, $relation, $options);
        $related->makeRelationship();

        if ($related->verifyRequestOrderBy($this->hasRequestStr(), $this->sortablesRelated ?? [])) {
            OrderByRelated::setOrderByRelated($related->getTableColumnByUrl());
        }

        return $query->autosort();
    }

    final public function scopeAutosort(Builder|BuilderE $query): Builder|BuilderE
    {
        $this->verifySortablesProperty();

        // "$this->getSqlOrderBy()" can be null (it is if in the "$sortables" property we have asked that it is not ordered by by default).
        $orderBy = OrderByRelated::getOrderByRelated() ?? $this->getSqlOrderBy();

        OrderByRelated::clearOrderByRelated();

        if ($orderBy) {
            return $query->orderBy($orderBy, $this->getSqlOrder());
        }

        return $query;
    }

    /*
    |--------------------------------------------------------------------------
    | Utils:
    |--------------------------------------------------------------------------
    */

    final public function getSqlOrderBy(): ?string
    {
        if ($this->hasRequestStr() && in_array(request()->orderby, $this->sortablesAs ?? [])) {
            return request()->orderby; // If is an alias: we don't want a prefix table.
        } elseif ($this->hasRequestStr() && in_array(request()->orderby, $this->sortables)) {
            $orderBy = request()->orderby;
        } else {
            $orderBy = Larasort::getDefaultSortable() ?? $this->sortables[0];
        }

        // if and elseif : if column (key) is assigned to a table.
        if (array_key_exists($orderBy, Larasort::getSortablesToTables())) {
            return Larasort::getSortablesToTables()[$orderBy];
        } elseif (property_exists($this, 'sortablesToTables') && array_key_exists($orderBy, $this->sortablesToTables)) {
            return $this->sortablesToTables[$orderBy];
        }

        return $orderBy !== null ? $this->getTable().'.'.$orderBy : null;
    }

    final public function getSqlOrder(): string
    {
        // Case 1:

        if (request()->has('order')) {
            return strtolower(request()->order) === 'desc' ? 'desc' : 'asc';
        }

        $orderBy = $this->getSqlOrderBy();

        // Case 2:

        // We manage: so that in "Larasort::setSortablesDefaultOrder" method whether or not it is possible to put the table in prefix.
        if (strpos((string) $orderBy, '.') !== false) {
            $ex = explode('.', (string) $orderBy);
            $orderByWithoutTable = $ex[1];

            if (in_array($orderByWithoutTable, Larasort::getSortablesDefaultOrder()['desc'])) {
                return 'desc';
            } elseif (in_array($orderByWithoutTable, Larasort::getSortablesDefaultOrder()['asc'])) {
                return 'asc';
            }
        }

        // Case 3:

        if (config('larasort.default_order') === 'desc') {
            return in_array($orderBy, Larasort::getSortablesDefaultOrder()['asc']) ? 'asc' : 'desc';
        }

        return in_array($orderBy, Larasort::getSortablesDefaultOrder()['desc']) ? 'desc' : 'asc';
    }

    private function hasRequestStr(): bool
    {
        return request()->has('orderby') && request()->orderby !== null;
    }

    /*
    |--------------------------------------------------------------------------
    | Getters:
    |--------------------------------------------------------------------------
    */

    /**
     * @return array<null|string>
     */
    final public function getSortables(): array
    {
        return $this->sortables;
    }

    /**
     * @return array<string>
     */
    final public function getSortablesAs(): array
    {
        return $this->sortablesAs ?? [];
    }

    /**
     * @return array<string>
     */
    final public function getSortablesRelated(): array
    {
        return $this->sortablesRelated ?? [];
    }

    /*
    |--------------------------------------------------------------------------
    | Security
    |--------------------------------------------------------------------------
    */

    private function verifySortablesProperty(): void
    {
        if (! property_exists($this, 'sortables')) {
            throw new LarasortException('The "$sortables" property must exist in the model.');
        }

        if (! is_array($this->sortables) || count($this->sortables) === 0) {
            throw new LarasortException('The "sortables" property must be an array with at least one element.');
        }
    }
}
