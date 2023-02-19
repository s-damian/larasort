<?php

namespace SDamian\Tests\Larasort\Fixtures\Models;

use Illuminate\Database\Eloquent\Model;
use SDamian\Larasort\AutoSortable;

/**
 * Model without table.
 * Model useful only for testing that "getSqlOrderBy" method of "AutoSortable" trait can return null.
 */
class Address extends Model
{
    use AutoSortable; // For Larasort

    /**
     * For Larasort
     * The attributes that are sortable.
     *
     * @var array<null|string>
     */
    private array $sortables = [
        null,
        'id',
        'name',
    ];
}
