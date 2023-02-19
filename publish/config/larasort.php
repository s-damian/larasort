<?php

/**
 * Default config.
 *
 * @author  Stephen Damian <contact@damian-freelance.fr>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 *
 * @link    https://github.com/s-damian/larasort
 */
return [

    /**
     * The default order (direction) of the ORDER BY.
     * Supported: "asc", "desc"
     */
    'default_order' => 'asc',

    /**
     * The default relationship column separator.
     * Example: "articles.title" means relation "articles" and column "title".
     */
    'relation_column_separator' => '.',

    /**
     * The default join type.
     * Supported - With MySQL and PostgreSQL: "join", "leftJoin", "rightJoin"
     * Supported - SQLite: "join", "leftJoin"
     */
    'default_join_type' => 'leftJoin',

];
