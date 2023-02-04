<p align="center">
<a href="https://github.com/s-damian/larasort">
<img src="https://raw.githubusercontent.com/s-damian/medias/main/package-logos/larasort.png" width="400">
</a>
</p>

# Column sorting for Laravel - Sortable - Sort by

[![Tests](https://github.com/s-damian/larasort/actions/workflows/tests.yml/badge.svg)](https://github.com/s-damian/larasort/actions/workflows/tests.yml)
[![Static analysis](https://github.com/s-damian/larasort/actions/workflows/static-analysis.yml/badge.svg)](https://github.com/s-damian/larasort/actions/workflows/static-analysis.yml)
[![Latest Stable Version](https://poser.pugx.org/s-damian/larasort/v/stable)](https://packagist.org/packages/s-damian/larasort)
[![License](https://poser.pugx.org/s-damian/larasort/license)](https://packagist.org/packages/s-damian/larasort)

## Larasort : Column sorting for Laravel - Sort easily

### Introduction - Larasort package

This package allows you to automate the ```ORDER BY``` of your SQL queries, as well as to automate the generation of sortable links.

This Open Source library allows to make **sortable columns** in an automated way with **Laravel**.

You have two packages in one: **Larasort** (for sorting **with** Eloquent ORM) and **LarasortManual** (for sorting **without** Eloquent ORM).

> Sort easily in an automated way 🚀

### Simple example with Larasort

* Example in Model:

```php
private array $sortables = [ // The attributes that are sortable.
    'email',
    'first_name',
    'created_at',
];
```

* Example in Controller:

```php
$customers = Customer::whereNotNull('confirmed_at')
    ->autosort() // Automate ORDER BY and its direction.
    ->paginate();
```

* Example in View (in blade template):

```html
@sortableLink('email', 'Email')
```

Example rendering of a link in a table:

[![Larasort](https://raw.githubusercontent.com/s-damian/medias/main/packages/larasort-th-example.webp)](https://github.com/s-damian/larasort)

### Author

This package is developed by [Stephen Damian](https://github.com/s-damian)

### Requirements

* PHP 8.0 || 8.1 || 8.2
* Laravel 8 || 9 || 10


## Summary

* [Installation](#installation)
* [Customization with "vendor:publish"](#customization-with-vendorpublish)
* [Larasort - For Eloquent ORM](#larasort---for-eloquent-orm)
  * [Basic usage](#basic-usage)
  * [Aliasing](#aliasing)
    * [Example with ->join()](#example-with--join)
  * [Relationships](#relationships)
    * [One To One](#one-to-one)
    * [One To Many](#one-to-many)
    * [Belongs To](#belongs-to)
    * [Relationships - Conventions](#relationships---conventions)
  * [For a column, specify its table](#for-a-column-specify-its-table)
  * [Put "desc" or "asc" by default for some columns](#put-desc-or-asc-by-default-for-some-columns)
  * [Clear Larasort static methods](#clear-larasort-static-methods)
  * [Larasort - API Doc](#larasort---api-doc)
    * [Model properties](#model-properties)
    * [Larasort class](#larasort-class)
    * [AutoSortable trait](#autosortable-trait)
    * [Blade directives](#blade-directives)
* [LarasortManual - For without Eloquent ORM](#larasortmanual---for-without-eloquent-orm)
  * [LarasortManual - Basic usage](#larasortmanual---basic-usage)
  * [LarasortManual - For a column, specify its table](#larasortmanual---for-a-column-specify-its-table)
  * [LarasortManual - Put "desc" or "asc" by default for some columns](#larasortmanual---put-desc-or-asc-by-default-for-some-columns)
  * [LarasortManual - API Doc](#larasortmanual---api-doc)
    * [LarasortManual class](#larasortmanual-class)
* [Support](#support)
* [License](#license)


## Installation

Installation via Composer:

```
composer require s-damian/larasort
```


## Customization with "vendor:publish"

### Custom Config and Lang and CSS

After installing the package, you have to run the ```vendor:publish``` command:

```
php artisan vendor:publish --provider="SDamian\Larasort\LarasortServiceProvider"
```

The ```vendor:publish``` command will generate these files:

* ```config/larasort.php```

* ```public/vendor/larasort/css/larasort.css``` (**you must include this CSS in your website**)

* ```public/vendor/larasort/images/order.webp```

You can of course customize these files.

### "vendor:publish" with "--tag" argument

Publish only ```config``` file:

```
php artisan vendor:publish --provider="SDamian\Larasort\LarasortServiceProvider" --tag=config
```

Publish only ```CSS``` file:

```
php artisan vendor:publish --provider="SDamian\Larasort\LarasortServiceProvider" --tag=css
```

Publish only ```images``` file:

```
php artisan vendor:publish --provider="SDamian\Larasort\LarasortServiceProvider" --tag=images
```


# Larasort - For Eloquent ORM

**Larasort** is useful when using the Eloquent ORM.

## Basic usage

First, your Model must use the ```AutoSortable``` Trait.

Then it is necessary that in your Model you declare ```$sortables```.
This property is useful for defining the columns (columns in your DB table) allowed to be sorted in the ```ORDER BY```.

PS: the 1st column of the array ```$sortables``` will be the column used by default for the SQL ```ORDER BY```.

Example:

```php
<?php

use SDamian\Larasort\AutoSortable;

class Customer extends Model
{
    use AutoSortable;
    
    /**
     * The attributes that are sortable.
     */
    private array $sortables = [
        'id', // "id" column will be the default column for the ORDER BY.
        'first_name', 
        'email',
        'created_at',
    ];
}
```

You can override the column used by default for ```ORDER BY``` with this static method:

PS: the advantage of using the ```setDefaultSortable``` method is that even if in the URL there are no ```?orderby={column}&order={direction}```,
the icon will appear the same in the link of the default column.

```php
<?php

use SDamian\Larasort\Larasort;

Larasort::setDefaultSortable('email') // "email" column will be the default column for the ORDER BY.
```

If by default (when in the URL there is no ```?orderby={column}```), you don't want to put ```ORDER BY``` to the SQL query:

```php
<?php

use SDamian\Larasort\AutoSortable;

class Customer extends Model
{
    use AutoSortable;
    
    /**
     * The attributes that are sortable.
     */
    private array $sortables = [
        null, // Will be null by default (by default there will be no ORDER BY).
        'id',
        'first_name', 
        'email',
        'created_at',
    ];
}
```

Then with eloquent, you can use the ```->autosort()``` magic method:

```php
<?php

use App\Models\Customer;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::whereNotNull('confirmed_at')
            ->autosort() // Automate ORDER BY and its direction.
            ->paginate();

        return view('customer.index', [
            'customers' => $customers,
        ]);
    }
}

```

And in the view you can do this in the ```thead``` of a ```table``` for example:

PS: You must put the CSS class ```with-larasort``` on a HTML tag which encloses the blade directive (on the ```table``` or ```thead``` tag by example).

```html
<thead class="with-larasort">
    <tr>
        <th>
            @sortableLink('first_name', 'First name')
        </th>
        <th>
            @sortableLink('email', 'Email')
        </th>
        <th>
            @sortableLink('created_at', 'Register on')
        </th>
    </tr>
</thead>
```

PS: 1st parameter is the ```column``` in database, 2nd parameter is the ```title``` (```label```).
The 2nd parameter is optional. If you don't specify pass, the label will be generated automatically based on the column name.

If you need to keep more control inside a **th**, as an equivalent you can replace ```@sortableLink``` by ```@sortableHref``` and ```@sortableIcon```. Example:

```html
<th>
    <a @sortableHref('email')>
        Email
        @sortableIcon('email')
    </a>
</th>
```

## Aliasing

If for some columns you do not want to specify the table in prefix, you must use the ```$sortablesAs``` property.

In a concrete case, aliases are especially useful when you make an SQL query with a join.

### Example with ->join()

* Example in a Customer Model:

```php
<?php

use SDamian\Larasort\AutoSortable;

class Customer extends Model
{
    use AutoSortable;
    
    /**
     * The attributes that are sortable.
     */
    private array $sortables = [
        'id',
        'first_name', 
        'email',
        'created_at',
    ];

    /**
     * The attributes that are sortable without table in prefix.
     */
    private array $sortablesAs = [
        'article_title', // Here.
    ];
}
```

* Example in a CustomerController:

```php
<?php

class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::select([
                'customers.*',
                'articles.title AS article_title', // Here.
            ])
            ->join(
                'articles',
                'customers.id', '=', 'articles.customer_id'
            )
            ->autosort() // Automate ORDER BY and its direction.
            ->paginate();

        return view('customer.index', [
            'customers' => $customers,
        ]);
    }
}
```

* Example in View (in blade template):

```html
@sortableLink('article_title', 'Article Title')
```


## Relationships

With **Larasort** you can automate the ```ORDER BY``` of your relations One To One and One To Many.

To do this, you can use the ```autosortWith``` method.

### One To One

In this example, a ```user``` has created one ```article```, and an ```article``` has been created by a single ```user```.

This therefore makes a **One To One** relationship between ```users``` and ```articles```.

* Example in User Model:

```php
<?php

/**
 * The attributes of its sortable relations.
 */
private array $sortablesRelated = [
    // Convention: {relationship name}{separator}{column in this relationship table}.
    'article.title',
];

public function article()
{
    return $this->hasOne(Article::class, 'user_id_created_at', 'id');
}
```

* Example in UserController:

```php
<?php

$users = User::autosortWith('article', [
        'join_type' => 'join', // Optional - "leftJoin" by default.
        'columns' => ['id', 'username', 'email', 'role'], // Optional - "*" by default.
        'related_columns' => ['title AS article_title', 'h1'], // Optional - "*" by default.
    ])
    ->paginate();
```

* Example in View (in blade template):

```html
@sortableLink('article.title', 'Article Title')
```

PS: for the 1st argument of ```@sortableLink```, use the same convention as in the ```$sortablesRelated``` property of the Model.

### One To Many

In this example, a ```user``` has created multiple ```articles```, and an ```article``` has been created by a single ```user```.

This therefore makes a **One To Many** relationship between ```users``` and ```articles``` (several articles per user, and only one user per article).

* Example in User Model:

```php
<?php

/**
 * The attributes of its sortable relations.
 */
private array $sortablesRelated = [
    // Convention: {relationship name}{separator}{column in this relationship table}.
    'articles.title',
];

public function articles()
{
    return $this->hasMany(Article::class, 'user_id_created_at', 'id');
}
```

* Example in UserController:

```php
<?php

$users = User::autosortWith('articles', [
        'join_type' => 'join', // Optional - "leftJoin" by default.
        'columns' => ['id', 'username', 'email', 'role'], // Optional - "*" by default.
        'related_columns' => ['title AS article_title', 'h1'], // Optional - "*" by default.
    ])
    ->paginate();
```

* Example in View (in blade template):

```html
@sortableLink('articles.title', 'Article Title')
```

PS: for the 1st argument of ```@sortableLink```, use the same convention as in the ```$sortablesRelated``` property of the Model.

### Belongs To

Whether for a **One To One** or **One To Many** relationship, you must put the **belongsTo** method in the Article Model.

* Example in Article Model:

```php
<?php

private array $sortablesRelated = [
    // Convention: {relationship name}{separator}{column in this relationship table}.
    'user.email',
];

public function user()
{
    return $this->belongsTo(User::class, 'user_id_created_at', 'id');
}
```

* Example in ArticleController:

```php
<?php

$articles = Article::autosortWith('user', [
        'join_type' => 'join', // Optional - "leftJoin" by default.
        'columns' => ['id', 'slug', 'h1', 'updated_at'], // Optional - "*" by default.
        'related_columns' => ['email AS user_email', 'first_name'], // Optional - "*" by default.
    ])
    ->paginate();
```

* Example in View (in blade template):

```html
@sortableLink('user.email', 'User Email')
```

PS: for the 1st argument of ```@sortableLink```, use the same convention as in the ```$sortablesRelated``` property of the Model.

### Relationships - Conventions

#### Model $sortablesRelated property

For the columns you put in the in the ```$sortablesRelated``` property,
the onventions is: ```{relationship name}{separator}{column in this relationship table}```

Larasort will use ```{relationship name}``` to do the ```ORDER BY``` on its table.

By default the separator is a period. If you wish, you can change it in the config with ```relation_column_separator```.

#### ->autosortWith() method options

To do the join, you must specify the name of the relation in the first parameter of ```->autosortWith()```.

Inside, you must pass the name of your relation (the name of the relation method that you put in your Model).
Larasort will use this name to do the ```join```.

##### Options:

PS:
If at the first parameter of ```->autosortWith()``` you put a relation name different from what you had put at ```{relationship name}``` of the property ```$sortablesRelated```,
the ```ORDER BY``` simply won't happen on the relationship.

* "```join_type```" (optional):

To make another joint than default (the one specified in the config), you can specify the ```join_type``` option.

* "```columns```" (optional):

If you want to specify the columns to ```SELECT``` for your Model, you can specify the ```columns``` option.

You can put either an array or a string.
Example with an array: ```['id', 'email', 'username']``` Example with a string: ```'id, email, username'```

By default the ```SELECT``` will be done on all the columns.

* "```related_columns```" (optional):

If you want to specify which columns to ```SELECT``` for your Model's relationship, you can specify the ```related_columns``` option.

You can put either an array or a string.
Example with an array: ```['title AS article_title', 'content']``` Example with a string: ```'title AS article_title, content'```

By default the ```SELECT``` will be done on all the columns.


## For a column, specify its table

With **Larasort** you can for columns, specify their table (this is useful when you make a SQL query with join).

By default, Larasort will do the ```ORDER BY``` on the table where the ```AutoSortable``` trait is included.

Let's take an example where in an SQL query you want to retrieve articles (from a ```articles``` table) and categories (from a ```categories``` table),
and that for these 2 tables you want to retrieve the ```id``` column.
But you want to do ```ORDER BY id``` on the ```categories``` table instead of on the ```articles``` table.

### You can solve this problem with these 2 solutions

#### Solution 1 - With $sortablesToTables property

The ```$sortablesToTables``` property can optionally be put in the Model:

```php
<?php

use SDamian\Larasort\AutoSortable;

class Article extends Model
{
    use AutoSortable;
    
    /**
     * The attributes that are sortable.
     */
    private array $sortables = [
        'id',
        'title',
        'updated_at',
    ];

    /**
     * The sortable attributes to which their table is specified.
     */
    private array $sortablesToTables = [
        'id' => 'categories.id', // Here.
    ];
}
```

#### Solution 2 - With Larasort::setSortablesToTables(array $sortablesToTables)

The ```Larasort::setSortablesToTables(array $sortablesToTables)``` method can optionally be put just before the SQL query where you will use ```->autosort()```
(in the Controller or in the Model, for example).

Example in a ArticleController:

```php
<?php

use SDamian\Larasort\Larasort;

class ArticleController extends Controller
{
    public function index()
    {
        Larasort::setSortablesToTables(['id' => 'categories.id']); // Here.

        // Here your SQL query with ->autosort()
        // Then the rest of the code...
    }
}
```

If the ```$sortablesToTables``` property and the ```Larasort::setSortablesToTables(array $sortablesToTables)``` method are used at the same time for the same column,
the ```Larasort::setSortablesToTables(array $sortablesToTables)``` metho method will override the ```$sortablesToTables``` property.

With these 2 solutions, the result of the SQL queries will be: ```ORDER BY `categories`.`id` ASC``` instead of ```ORDER BY `articles`.`id` ASC ```

## Put "desc" or "asc" by default for some columns

It is possible for some columns,
that the order (the direction of the ```ORDER BY```) to want it to be by default (or on the 1st click on its link) at ```desc``` instead of ```asc```.

This can optionally be put just before the SQL query where you will use ```->autosort()``` (in the Controller or in the Model, for example).

Example in a InvoiceController:

```php
<?php

use SDamian\Larasort\Larasort;

class InvoiceController extends Controller
{
    public function index()
    {
        Larasort::setSortablesDefaultOrder([
            'desc' => ['id', 'created_at', 'price_with_vat'], // Here.
        ]);

        // Here your SQL query with ->autosort()
        // Then the rest of the code...
    }
}
```

### If you change "default_order" at "config/larasort.php" file - Put "asc" by default for some columns

In the ```config/larasort.php``` config file, you can change the value of ```default_order``` (which defaults to ```asc```).

If you do this: it is possible for some columns, than the order of wanting it to be at ```asc``` instead of ```desc```.

Example in a InvoiceController:

```php
<?php

use SDamian\Larasort\Larasort;

class InvoiceController extends Controller
{
    public function index()
    {
        Larasort::setSortablesDefaultOrder([
            'asc' => ['customer_email', 'customer_first_name'], // Here.
        ]);

        // Here your SQL query with ->autosort()
        // Then the rest of the code...
    }
}
```

## Clear Larasort static methods

If you need to, you can clear (reset) the static methods of Larasort:

```php
<?php

Larasort::clearDefaultSortable();

Larasort::clearSortablesToTables();

Larasort::clearSortablesDefaultOrder();
```

## Larasort - API Doc

### Model properties

| Type  | Property           | Description |
| ----- | ------------------ | ----------- |
| array | $sortables         | Define columns that are sortable. |
| array | $sortablesAs       | Define alias columns that are sortable. |
| array | $sortablesToTables | For column(s), specify its table. |

### Larasort class

For ```SDamian\Larasort\Larasort``` class:

| Return type | Method                                                   | Description |
| ----------- | -------------------------------------------------------- | ----------- |
| void        | ::setDefaultSortable(string $defaultSortable)            | Change the default column (for the SQL ```ORDER BY```). |
| void        | ::clearDefaultSortable()                                 | Clear "setDefaultSortable" method. |
| void        | ::setSortablesToTables(array $sortablesToTables)         | For column(s), specify its table. |
| void        | ::clearSortablesToTables()                               | Clear "setSortablesToTables" method. |
| void        | ::setSortablesDefaultOrder(array $sortablesDefaultOrder) | Assign default order ("desc" or "asc") for some columns. |
| void        | ::clearSortablesDefaultOrder()                           | Clear "setSortablesDefaultOrder" method. |

### AutoSortable trait

For ```SDamian\Larasort\AutoSortable``` trait:

| Return type | Method                        | Description                                      |
| ----------- | ----------------------------- | ------------------------------------------------ |
| Builder     | scopeAutosort(Builder $query) | scope to generate the ```ORDER BY``` of the SQL query. |

### Blade directives

| Return type | Directive                                    | Description                                     | Return example |
| ----------- | -------------------------------------------- | ----------------------------------------------- |--- |
| string      | @sortableUrl(string $column)                | Returns the URL of a column.            | ```http://www.website.com/utilisateurs?orderby=email&order=asc``` |
| string      | @sortableHref(string $column)                | Returns the href (with its URL in it )of a column.            | ```href='http://www.website.com/utilisateurs?orderby=email&order=asc'``` |
| string      | @sortableIcon(string $column)                | Returns the icon (image) of a column, in the correct order. | ```<span class="larasort-icon-n-1"></span>``` |
| string      | @sortableLink(string $column, string $label) | Return link of a column = href + label + icon.              | ```<a href="http://www.website.com/utilisateurs?orderby=email&amp;order=asc">Email<span class="larasort-icon-n-1"></span></a>``` |


# LarasortManual - For without Eloquent ORM

**Larasort** is useful when you weren't using the Eloquent ORM.

If you want to do a manual SQL query (or if you want to do a file listing), an alternative exists: **LarasortManual**

## LarasortManual - Basic usage

With **LarasortManual**, the ```setSortables(array $sortables)``` method is useful to define the columns allowed to be sorted in the ```ORDER BY```. Simple example:

```php
<?php

use SDamian\Larasort\Manual\LarasortManual;

class CustomerController extends Controller
{
    public function index()
    {
        $larasortMan = new LarasortManual();
        $larasortMan->setSortables(['id', 'first_name', 'email', 'created_at']); // Here.
        $resultLarasortMan = $larasortMan->get();

        $customers = DB::select('
            SELECT *
            FROM customers
            ORDER BY '.$resultLarasortMan['order_by'].' '.$resultLarasortMan['order'].'
        ');

        return view('customer.index', [
            'customers' => $customers,
            'larasortManAttrs' => $resultLarasortMan['attrs'],
        ]);
    }
}
```

And in the view you can do this in the **thead** of a **table** for example:

```html
<thead class="with-larasort">
    <tr>
        <th>
            <a {!! $larasortManAttrs['first_name']['href'] !!}>
                First name
                {!! $larasortManAttrs['first_name']['icon'] !!}
            </a>
        </th>
        <th>
            <a {!! $larasortManAttrs['email']['href'] !!}>
                Email
                {!! $larasortManAttrs['email']['icon'] !!}
            </a>
        </th>
        <th>
            <a {!! $larasortManAttrs['created_at']['href'] !!}>
                Register on
                {!! $larasortManAttrs['created_at']['icon'] !!}
            </a>
        </th>
        <th>Actions</th>
    </tr>
</thead>
```
PS: if you wish, you can also have access to ```$larasortManAttrs['column_name']['url']```

## LarasortManual - For a column, specify its table

With **LarasortManual** also you can for columns, specify their table (this is useful when you make a SQL query with join).

Unlike **Larasort** which makes the SQL query on the table where the ```AutoSortable``` trait is included,
by default, **LarasortManual** will do the ```ORDER BY column``` without specifying a table in prefix.

So, when you join multiple tables, if you ```SELECT``` the same column name on several tables, you can end up with an error like: *"Integrity constraint violation: 1052 Column '{colomn}' in order clause is ambiguous"*.

Let's take an example where in an SQL query you want to retrieve articles (from a ```articles``` table) and categories (from a ```categories``` table),
and that for these 2 tables you want to retrieve the ```id``` column. And you want to do ```ORDER BY id``` on the ```categories``` table.

You can do this with the ```$larasortMan->setSortablesToTables(array $sortablesToTables)``` method. Example:

```php
<?php

use SDamian\Larasort\Manual\LarasortManual;

class ArticleController extends Controller
{
    public function index()
    {
        $larasortMan = new LarasortManual();
        $larasortMan->setSortables(['id', 'title', 'created_at']);
        $larasortMan->setSortablesToTables(['id' => 'categories.id']); // Here.
        $resultLarasortMan = $larasortMan->get();

        // Here your SQL query with $resultLarasortMan['order_by'] and $resultLarasortMan['order']
        // Then the rest of the code...
    }
}
```

```$resultLarasortMan['order_by']``` will generate the SQL query ```ORDER BY `categories`.`id` ASC``` instead of ```ORDER BY `id` ASC```

## LarasortManual - Put "desc" or "asc" by default for some columns

With **LarasortManual** also you can for some columns, have the order (the direction of ORDER BY) default (or on the 1st click on its link) to ```desc``` instead of ```asc```.

You can do this with the ```$larasortMan->setSortablesDefaultOrder(array $sortablesDefaultOrder)``` method. Example:

```php
<?php

use SDamian\Larasort\Manual\LarasortManual;

class InvoiceController extends Controller
{
    public function index()
    {
        $larasortMan = new LarasortManual();
        $larasortMan->setSortables(['id', 'ref', 'customer_email', 'created_at', 'price_with_vat']);
        $larasortMan->setSortablesDefaultOrder([
            'desc' => ['id', 'created_at', 'price_with_vat'], // Here.
        ]);
        $resultLarasortMan = $larasortMan->get();

        // Here your SQL query with $resultLarasortMan['order_by'] and $resultLarasortMan['order']
        // Then the rest of the code...
    }
}
```

### If you change "default_order" at "config/larasort.php" file - Put "asc" by default for some columns

You can do this in exactly the same way as with Larasort. By doing something like this:

```php
<?php

$larasortMan->setSortablesDefaultOrder([
    'asc' => ['customer_email', 'customer_first_name'], // Here.
]);
```

## LarasortManual - API Doc

### LarasortManual class

For ```SDamian\Larasort\Manual\LarasortManual``` class:

| Return type | Method                                                 | Description |
| ----------- | ------------------------------------------------------ | ----------- |
| void        | setSortables(array $sortables)                         | To specify sortable columns. |
| void        | setSortablesToTables(array $sortablesToTables)         | For column(s), specify its table. |
| void        | setSortablesDefaultOrder(array $sortablesDefaultOrder) | Assign default order ("desc" or "asc") for some columns. |
| array       | get() | Return the result of LarasortManual instance. |


## Support

### Bugs and security Vulnerabilities

If you discover a bug or a security vulnerability, please send a message to Stephen. Thank you.

All bugs and all security vulnerabilities will be promptly addressed.


## License

This project is an Open Source package under the MIT license. See the LICENSE file for details.
