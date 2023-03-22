<?php

declare(strict_types=1);

namespace SDamian\Larasort\Traits;

/**
 * This Trait is useful to avoid duplicating code between Larasort and LarasortManual.
 *
 * @author  Stephen Damian <contact@damian-freelance.fr>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 *
 * @link    https://github.com/s-damian/larasort
 */
trait UtilsTrait
{
    /**
     * @param  array<string>  $sortablesDefaultOrder
     * @return array<string>
     */
    private static function getSortablesDefaultOrderWithoutTable(array $sortablesDefaultOrder): array
    {
        $sortablesToReturn = [];
        foreach ($sortablesDefaultOrder as $sortable) {
            // Here it is not necessary to specify its table as a prefix. But if the user puts the table as a prefix, we manage it by deleting it.
            if (strpos($sortable, '.') !== false) {
                $ex = explode('.', $sortable);
                $sortable = $ex[1];
            }

            $sortablesToReturn[] = $sortable;
        }

        return $sortablesToReturn;
    }
}
