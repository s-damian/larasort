<?php

namespace SDamian\Larasort\Support;

use SDamian\Larasort\Exception\LarasortException;

/**
 * Larasort - This Trait is useful for generate the href and CSS class attributes.
 *
 * @author  Stephen Damian <contact@devandweb.fr>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @link    https://github.com/s-damian/larasort
 */
class Security
{
    /**
     * @param array<mixed> $options
     */
    public static function verifyScopeAutosortOptions(array $options = []): void
    {
        $allowedOptions = ['related', 'columns', 'related_columns', 'join_type'];
        foreach ($options as $key => $values) {
            if (! in_array($key, $allowedOptions)) {
                throw new LarasortException($key.' is not an option allowed.');
            }
        }
    }

    /**
     * @param array<mixed> $options
     */
    public static function verifyKeyIsAscOrDescAndValueIsArray(array $options = []): void
    {
        $allowedOptions = ['asc', 'desc'];
        foreach ($options as $key => $values) {
            if (! in_array($key, $allowedOptions)) {
                throw new LarasortException($key.' is not an option allowed. Only "asc", or "desc" are allowed.');
            } elseif (! is_array($values)) {
                throw new LarasortException('Value of "'.$key.'" must be an array.');
            }
        }
    }
}
