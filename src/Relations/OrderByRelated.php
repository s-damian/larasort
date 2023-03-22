<?php

declare(strict_types=1);

namespace SDamian\Larasort\Relations;

/**
 * Larasort - This class works with the "autosortWith" scope and with the "Related" class.
 *
 * @author  Stephen Damian <contact@damian-freelance.fr>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 *
 * @link    https://github.com/s-damian/larasort
 */
final class OrderByRelated
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
