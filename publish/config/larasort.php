<?php

/**
 * @author  Stephen Damian <contact@devandweb.fr>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @link    https://github.com/s-damian/larasort
 */
return [

    /**
     * The default order (direction) of ORDER BY.
     * Supported: "asc", "desc"
     */
    'default_order' => 'asc',

    /**
     * Default relationship column separator.
     * Example: "article.title" means relation "article" and column "title".
     */
    'relation_column_separator' => '.',

    /**
     * The default join type.
     * Supported: "join", "leftJoin", "rightJoin"
     */
    'default_join_type' => 'leftJoin',

];
