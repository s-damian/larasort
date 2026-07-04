<?php

declare(strict_types=1);

namespace SDamian\Larasort;

/**
 * Larasort - This class is useful for generating the href and CSS class attributes.
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
            $order = request()->has('order') && request()->order !== null && mb_strtolower(request()->order) === 'asc'
                ? 'desc'
                : 'asc';
        } elseif ((! request()->has('orderby') || request()->orderby === null) && $column === self::getDefaultSortableWithoutTable()) {
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

    final public static function getUrlV2(string $column, string $ascOrDesc): string
    {
        if ($ascOrDesc === 'asc') {
            $order = 'asc';
        } else {
            $order = 'desc';
        }

        return request()->fullUrlWithQuery(['orderby' => $column, 'order' => $order]);
    }

    final public static function getHref(string $column): string
    {
        return 'href="'.self::getUrl($column).'"';
    }

    final public static function getHrefV2(string $column, string $ascOrDesc): string
    {
        return 'href="'.self::getUrlV2($column, $ascOrDesc).'"';
    }

    final public static function getIcon(string $column): string
    {
        if (request()->has('orderby') && request()->orderby === $column) {
            $class = request()->has('order') && request()->order !== null && mb_strtolower(request()->order) === 'asc'
                ? 'larasort-icon-1'
                : 'larasort-icon-2';
        } elseif ((! request()->has('orderby') || request()->orderby === null) && $column === self::getDefaultSortableWithoutTable()) {
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

    final public static function getIconV2(string $ascOrDesc): string
    {
        if ($ascOrDesc === 'asc') {
            $suffix = request()->order !== null && mb_strtolower(request()->order) === 'asc' ? ' v2-active' : '';
            $class = 'larasort-icon-1_v2'.$suffix;
        } else {
            $suffix = request()->order !== null && mb_strtolower(request()->order) === 'desc' ? ' v2-active' : '';
            $class = 'larasort-icon-2_v2'.$suffix;
        }

        return '<span class="'.$class.'"></span>';
    }

    final public static function getLink(string $column, ?string $label = null): string
    {
        $labelToShow = $label ?? ucfirst(str_replace(['_', config('larasort.relation_column_separator')], ' ', $column));

        $html = '';

        $html .= '<a '.self::getHref($column).'>';
        $html .= $labelToShow;
        $html .= self::getIcon($column);
        $html .= '</a>';

        return $html;
    }

    final public static function getLinkV2(string $column, ?string $label = null): string
    {
        $labelToShow = $label ?? ucfirst(str_replace(['_', config('larasort.relation_column_separator')], ' ', $column));

        $html = '';

        $html .= $labelToShow;
        $html .= '<a '.self::getHrefV2(column: $column, ascOrDesc: 'asc').'>';
        $html .= self::getIconV2(ascOrDesc: 'asc');
        $html .= '</a>';
        $html .= '<a '.self::getHrefV2(column: $column, ascOrDesc: 'desc').'>';
        $html .= self::getIconV2(ascOrDesc: 'desc');
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
