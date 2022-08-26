<?php

namespace SDamian\Larasort\Relations;

use Illuminate\Database\Query\Builder;
use SDamian\Larasort\Exception\LarasortException;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\Builder as BuilderE;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    private string $relation;

    /**
     * @var array<mixed>
     */
    private array $options;

    /**
     * @param array<mixed> $options
     */
    public function __construct(object $model, Builder|BuilderE $query, string $relation, array $options)
    {
        $this->model = $model;
        $this->query = $query;
        $this->relation = $relation;
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
        return $hasRequestStr && strpos((string) request()->orderby, config('larasort.relation_column_separator')) !== false
            && in_array(request()->orderby, $sortablesRelated);
    }

    /*
    |--------------------------------------------------------------------------
    | Generate SQL query
    |--------------------------------------------------------------------------
    */

    final public function makeRelationship(): void
    {
        // Here we use the "getRelationByOptions" method, because we want to join even if there is no relation in the URL.
        $relation = $this->query->getRelation($this->getRelationByOptions());

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
        switch (true) {
            case $relation instanceof HasOne:
            case $relation instanceof HasMany:
                $relatedPrimaryKey = $relation->getQualifiedForeignKeyName(); // foreign_key of the table of the related Model.
                $modelPrimaryKey  = $relation->getQualifiedParentKeyName(); // primary_key of the table of this Model.
                break;
            case $relation instanceof BelongsTo:
                $relatedPrimaryKey = $relation->getQualifiedOwnerKeyName(); // foreign_key of the table of this Model.
                $modelPrimaryKey  = $relation->getQualifiedForeignKeyName();  // primary_key of the table of the related Model.
                break;
            default:
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
        return $this->options['join_type'] ?? config('larasort.default_join_type');
    }

    /*
    |--------------------------------------------------------------------------
    | Utils
    |--------------------------------------------------------------------------
    */

    private function getRelatedTable(): string
    {
        $relation = $this->query->getRelation($this->getRelationByOptions());
        $relatedModel = $relation->getRelated();

        return $relatedModel->getTable();
    }

    private function getRelationByOptions(): string
    {
        return $this->relation;
    }

    private function getRelationByUrl(): string
    {
        $ex = explode(config('larasort.relation_column_separator'), request()->orderby);

        return $ex[0];
    }

    final public function getTableColumnByUrl(): ?string
    {
        // We do the ORDER BY on the relation only if: if the relation name passed in $this->relation is the same as the one in the URL.
        if ($this->getRelationByUrl() !== $this->getRelationByOptions()) {
            return null;
        }

        $ex = explode(config('larasort.relation_column_separator'), request()->orderby);

        return  $this->getRelatedTable().'.'.$ex[1];
    }
}
