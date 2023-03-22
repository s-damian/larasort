<?php

declare(strict_types=1);

namespace SDamian\Larasort;

/**
 * Larasort - This class is useful for generate the href and CSS class attributes.
 *
 * @author  Stephen Damian <contact@damian-freelance.fr>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 *
 * @link    https://github.com/s-damian/larasort
 */
class LarasortLink
{
    final public static function getUrl(string $column): string
    {
        if (request()->has('orderby') && request()->orderby === $column) {
            $order = request()->has('order') && strtolower(request()->order) === 'asc'
                ? 'desc'
                : 'asc';
        } elseif (! request()->has('orderby') && $column === self::getDefaultSortableWithoutTable()) {
            if (config('larasort.default_order') === 'desc') {
                $order = in_array($column, Larasort::getSortablesDefaultOrder()['asc'])
                    ? 'desc'
                    : 'asc';
            } else {
                $order = in_array($column, Larasort::getSortablesDefaultOrder()['desc'])
                    ? 'asc'
                    : 'desc';
            }
        } else {
            if (config('larasort.default_order') === 'desc') {
                $order = in_array($column, Larasort::getSortablesDefaultOrder()['asc'])
                    ? 'asc'
                    : 'desc';
            } else {
                $order = in_array($column, Larasort::getSortablesDefaultOrder()['desc'])
                    ? 'desc'
                    : 'asc';
            }
        }

        return request()->fullUrlWithQuery(['orderby' => $column, 'order' => $order]);
    }

    final public static function getHref(string $column): string
    {
        return 'href="'.self::getUrl($column).'"';
    }

    final public static function getIcon(string $column): string
    {
        if (request()->has('orderby') && request()->orderby === $column) {
            $class = request()->has('order') && strtolower(request()->order) === 'asc'
                ? 'larasort-icon-1'
                : 'larasort-icon-2';
        } elseif (! request()->has('orderby') && $column === self::getDefaultSortableWithoutTable()) {
            if (config('larasort.default_order') === 'desc') {
                $class = in_array($column, Larasort::getSortablesDefaultOrder()['desc'])
                    ? 'larasort-icon-1'
                    : 'larasort-icon-2';
            } else {
                $class = in_array($column, Larasort::getSortablesDefaultOrder()['desc'])
                    ? 'larasort-icon-2'
                    : 'larasort-icon-1';
            }
        } else {
            if (config('larasort.default_order') === 'desc') {
                $class = in_array($column, Larasort::getSortablesDefaultOrder()['asc'])
                    ? 'larasort-icon-n-1'
                    : 'larasort-icon-n-2';
            } else {
                $class = in_array($column, Larasort::getSortablesDefaultOrder()['desc'])
                    ? 'larasort-icon-n-2'
                    : 'larasort-icon-n-1';
            }
        }

        return '<span class="'.$class.'"></span>';
    }

    final public static function getLink(string $column, string $label = null): string
    {
        $labelToShow = $label ?? ucfirst(str_replace(['_', config('larasort.relation_column_separator')], ' ', $column));

        $html = '';

        $html .= '<a '.self::getHref($column).'>';
        $html .= $labelToShow;
        $html .= self::getIcon($column);
        $html .= '</a>';

        return $html;
    }

    private static function getDefaultSortableWithoutTable(): ?string
    {
        $defaultSortable = Larasort::getDefaultSortable();

        if (strpos((string) $defaultSortable, '.') !== false) {
            $ex = explode('.', (string) $defaultSortable);
            $defaultSortableWithoutTable = $ex[1];

            return $defaultSortableWithoutTable;
        }

        return $defaultSortable;
    }
}
