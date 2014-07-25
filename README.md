Memory Component for Orchestra Platform 2
==============

Memory Component handles runtime configuration either using "in memory" Runtime or database using Cache, Fluent Query Builder or Eloquent ORM. Instead of just allowing static configuration to be used, Memory Component allow those configuration to be persistent in between request by utilising multiple data storage option either through cache or database.

[![Latest Stable Version](https://poser.pugx.org/orchestra/memory/v/stable.png)](https://packagist.org/packages/orchestra/memory) 
[![Total Downloads](https://poser.pugx.org/orchestra/memory/downloads.png)](https://packagist.org/packages/orchestra/memory) 
[![Build Status](https://travis-ci.org/orchestral/memory.svg?branch=2.2)](https://travis-ci.org/orchestral/memory) 
[![Coverage Status](https://coveralls.io/repos/orchestral/memory/badge.png?branch=2.2)](https://coveralls.io/r/orchestral/memory?branch=2.2) 
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/orchestral/memory/badges/quality-score.png?b=2.2)](https://scrutinizer-ci.com/g/orchestral/memory/) 

## Version Compatibility

Laravel    | Memory
:----------|:----------
 4.0.x     | 2.0.x
 4.1.x     | 2.1.x
 4.2.x     | 2.2.x

## Installation

To install through composer, simply put the following in your `composer.json` file:

```json
{
	"require": {
		"orchestra/memory": "2.2.*"
	}
}
```

And then run `composer install` from the terminal.

### Quick Installation

Above installation can also be simplify by using the following command:

```
composer require "orchestra/memory=2.2.*"
```

## Configuration

Next add the service provider in `app/config/app.php`.

```php
'providers' => array(
	
	// ...
	
	'Orchestra\Memory\MemoryServiceProvider',

	'Orchestra\Memory\CommandServiceProvider',
),
```

### Aliases

You might want to add `Orchestra\Support\Facades\Memory` to class aliases in `app/config/app.php`:

```php
'aliases' => array(

	// ...

	'Orchestra\Memory' => 'Orchestra\Support\Facades\Memory',
),
```

### Migrations

Before we can start using `Orchestra\Memory`, please run the following:

```bash
php artisan memory:migrate
```

> The command utility is enabled via `Orchestra\Memory\CommandServiceProvider`.

## Resources

* [Documentation](http://orchestraplatform.com/docs/latest/components/memory)
* [Change Log](http://orchestraplatform.com/docs/latest/components/memory/changes#v2-2)
