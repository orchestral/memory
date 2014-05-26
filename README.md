Orchestra Platform Memory Component
==============

`Orchestra\Memory` handles runtime configuration either using 'in memory' Runtime or database using Cache, Fluent Query Builder or Eloquent ORM. Instead of just allowing static configuration to be used, Orchestra\Memory allow those configuration to be persistent in between request by utilizing multiple data storage option either through cache or database.

[![Latest Stable Version](https://poser.pugx.org/orchestra/memory/v/stable.png)](https://packagist.org/packages/orchestra/memory) 
[![Total Downloads](https://poser.pugx.org/orchestra/memory/downloads.png)](https://packagist.org/packages/orchestra/memory) 
[![Build Status](https://travis-ci.org/orchestral/memory.svg?branch=2.2)](https://travis-ci.org/orchestral/memory) 
[![Coverage Status](https://coveralls.io/repos/orchestral/memory/badge.png?branch=2.2)](https://coveralls.io/r/orchestral/memory?branch=2.2) 
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/orchestral/memory/badges/quality-score.png?s=1f4d932ad48712a5dd811bbd33a0602966d3ff2b)](https://scrutinizer-ci.com/g/orchestral/memory/) 

## Quick Installation

To install through composer, simply put the following in your `composer.json` file:

```json
{
	"require": {
		"orchestra/memory": "2.2.*"
	}
}
```

Next add the service provider in `app/config/app.php`.

```php
'providers' => array(
	
	// ...
	
	'Orchestra\Memory\MemoryServiceProvider',

	'Orchestra\Memory\CommandServiceProvider',
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
