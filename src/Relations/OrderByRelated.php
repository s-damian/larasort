<?php

namespace SDamian\Larasort\Relations;

/**
 * Larasort - This class works with the "autosortWith" scope and with the "Related" class.
 *
 * @author  Stephen Damian <contact@devandweb.fr>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @link    https://github.com/s-damian/larasort
 */
class OrderByRelated
{
    private static ?string $orderByRelated = null;

    final public static function setOrderByRelated(?string $orderByRelated): void
    {
        self::$orderByRelated = $orderByRelated;
    }

    final public static function getOrderByRelated(): ?string
    {
        return self::$orderByRelated;
    }

    final public static function clearOrderByRelated(): void
    {
        self::$orderByRelated = null;
    }
}
