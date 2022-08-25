<?php

namespace SDamian\Tests\Larasort\Fixtures\Models;

use SDamian\Larasort\AutoSortable;
use Illuminate\Database\Eloquent\Model;

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
