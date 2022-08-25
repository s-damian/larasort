<?php

namespace SDamian\Larasort\Relations;

use Illuminate\Database\Query\Builder;
use SDamian\Larasort\Exception\LarasortException;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Builder as BuilderE;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * Larasort - This Trait is useful for generate the href and CSS class attributes.
 *
 * @author  Stephen Damian <contact@devandweb.fr>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @link    https://github.com/s-damian/larasort
 */
class Related
{
    private object $model;

    private Builder|BuilderE $query;

    /**
     * @var array<mixed>
     */
    private array $options;

    /**
     * @param array<mixed> $options
     */
    public function __construct(object $model, Builder|BuilderE $query, array $options)
    {
        $this->model = $model;
        $this->query = $query;
        $this->options = $options;
    }

    /*
    |--------------------------------------------------------------------------
    | Security
    |--------------------------------------------------------------------------
    */

    /**
     * @param array<string> $sortablesRelated
     */
    final public function verifyRequestOrderBy(bool $hasRequestStr, array $sortablesRelated): bool
    {
        return $hasRequestStr && strpos((string) request()->orderby, ':') !== false && in_array(request()->orderby, $sortablesRelated);
    }

    /*
    |--------------------------------------------------------------------------
    | Generate SQL query
    |--------------------------------------------------------------------------
    */

    final public function makeRelationship(): void
    {
        $relation = $this->query->getRelation($this->options['related']);

        [$relatedPrimaryKey, $modelPrimaryKey] = $this->getRelatedKeys($relation);

        // Assign columns to Models.
        $this->setColumnsToModel()->setColumnsToRelated();

        // Make the join.
        $this->query->{$this->getJoin()}($this->getRelatedTable(), $modelPrimaryKey, '=', $relatedPrimaryKey);
    }

    /**
     * @return array<string>
     */
    private function getRelatedKeys(Relation $relation): array
    {
        if ($relation instanceof HasOne) {
            $relatedPrimaryKey = $relation->getQualifiedForeignKeyName(); // foreign_key of the table of the related Model.
            $modelPrimaryKey  = $relation->getQualifiedParentKeyName(); // primary_key of the table of this Model.
        } elseif ($relation instanceof BelongsTo) {
            $relatedPrimaryKey = $relation->getQualifiedOwnerKeyName(); // foreign_key of the table of this Model.
            $modelPrimaryKey  = $relation->getQualifiedForeignKeyName();  // primary_key of the table of the related Model.
        } else {
            throw new LarasortException('Error with relation instanceof.');
        }

        return [$relatedPrimaryKey, $modelPrimaryKey];
    }

    private function setColumnsToModel(): self
    {
        if (isset($this->options['columns'])) {
            $columns = [];
            foreach ($this->options['columns'] as $related_column) {
                $columns[] = $this->model->getTable().'.'.$related_column;
            }
        } else {
            $columns = $this->model->getTable().'.*';
        }

        $this->query->select($columns);

        return $this;
    }

    private function setColumnsToRelated(): void
    {
        if (isset($this->options['related_columns'])) {
            $columnsForRelated = [];
            foreach ($this->options['related_columns'] as $related_column) {
                $columnsForRelated[] = $this->getRelatedTable().'.'.$related_column;
            }
        } else {
            $columnsForRelated = $this->getRelatedTable().'.*';
        }

        $this->query->addSelect($columnsForRelated);
    }

    private function getJoin(): string
    {
        return $this->options['join_type'] ?? 'leftJoin';
    }

    /*
    |--------------------------------------------------------------------------
    | Generate SQL string
    |--------------------------------------------------------------------------
    */

    final public function getSqlOrderBy(string $requestOrderBy): string
    {
        $ex = explode(':', $requestOrderBy);

        return $this->getRelatedTable().'.'.$ex[1];
    }

    /*
    |--------------------------------------------------------------------------
    | Utils
    |--------------------------------------------------------------------------
    */

    /**
     * Get table of Model related.
     */
    private function getRelatedTable(): string
    {
        $relation = $this->query->getRelation($this->options['related']);

        $relatedModel = $relation->getRelated();

        return $relatedModel->getTable();
    }
}
