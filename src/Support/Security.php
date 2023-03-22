<?php

declare(strict_types=1);

namespace SDamian\Larasort\Support;

use SDamian\Larasort\Exception\LarasortException;

/**
 * This ice is useful to manage some securities.
 *
 * @author  Stephen Damian <contact@damian-freelance.fr>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 *
 * @link    https://github.com/s-damian/larasort
 */
final class Security
{
    /**
     * @param  array<mixed>  $options
     */
    final public static function verifyScopeAutosortOptions(array $options = []): void
    {
        $allowedOptions = ['columns', 'related_columns', 'join_type'];
        foreach ($options as $key => $values) {
            if (! in_array($key, $allowedOptions)) {
                throw new LarasortException($key.' is not an option allowed.');
            }
        }
    }

    /**
     * @param  array<mixed>  $options
     */
    final public static function verifyKeyIsAscOrDescAndValueIsArray(array $options = []): void
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
