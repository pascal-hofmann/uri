Bakame.url
======

The Bakame Url package provides simple and intuitive classes and methods to create and manage Urls in PHP. 

This package is compliant with [PSR-0][], [PSR-1][], and [PSR-2][].

[PSR-0]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md
[PSR-1]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md
[PSR-2]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md

Getting Started
===============

Install
-------

You may install the Bakame Url package with Composer (recommended) or manually.

System Requirements
-------------------

You need **PHP >= 5.3.0** to use Bakame Url but the latest stable version of PHP is recommended.

Instantiation
-------------

The easiest way to get started is to added `'/path/to/Bakame/Entity/src'` to your PSR-0 compliant Autoloader. One added to the autoload you can instantiate your url with 3 differents methods as explain below:

```php
<?php


use Bakame\Url\Factory;

//Method 1 : from a given string
$url = Factory::createFromString('http://www.example.com'); // you've created a new Url object from this string 

//Method 2: from the current PHP page
$url = Factory::createFromServer($_SERVER); //don't forget to provide the $_SERVER array

//Method 3: a "naked" URL
$url = Factory::create();
```

These 3 methods will all return a valid `Bakame\Url\Url`. This is the main object we will be using to manipulate the url.


Basic Usage
------------

Manipulating the Url is simple with chaining, look at the example below:

```php

$url = Factory::createFromString('http://www.example.com');
$url
    ->setQuery('computer', 'os') //adding on query data
    ->setQuery(['foo' => 'bar', 'bar' => 'baz']) //add more query data using an array
    ->setPath('windows') //add a directory path named 'windows' at the end of the URL path
    ->setPath('linux', 'prepend', 'windows') //adding linux directory path before 'window'
    ->setPath('iOS', 'append', 'windows')  //adding iOS directory path after 'window'
    ->setAuth(['user' => 'john', 'pass' => 'doe']) //adding auth information
    ->setScheme('https')
    ->setPort(443)
    ->unsetHost() //remove any host information if present
    ->setHost(['api', 'ejamplo', 'com']);

echo $url; // will output https://john:doe@api.ejamplo.com:443/linux/windows/iOS?computer=os&foo=bar&bar=baz
```

Advanced Usage
---------------

The Bakame.url package comes bundle with Utility classes that can help you manipulate a single portion of you url. Let's say that you are only interested in modifying the query string from a given URL. You can proceed like so:

```php 
use Bakame\Url\Query;

$string = $_SERVER['QUERY_STRING'];

$query = new Query($string);

$query->getQuery(); //will return the data in form of an array
$query->clearQuery(); //will empty the query string
$query
    ->set('toto', 'leheros')
    ->set(['foo' => 'bar', 'bar' => 'baz']);

$string = $query->__toString(); // $string is now equals to toto=leheros&foo=bar&bar=baz

```

There are six (6) utility class for each URL parts:

* `Bakame\Url\Scheme` Manipulate the `scheme` component
* `Bakame\Url\Auth` Manipulate the `user` and `pass` components **together**
* `Bakame\Url\Segment` Manipulate the `path` and the `host` components **separately**. The difference between the 2 components is their separator. The `host` component uses the `.` while the `path` component uses the `/`. 
* `Bakame\Url\Port`  Manipulate the `port` component
* `Bakame\Url\Query`  Manipulate the `query` component
* `Bakame\Url\Fragment`  Manipulate the `fragment` component

Please refer to each class documentation to see what they can or can not do.