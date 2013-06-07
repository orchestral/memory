Orchestra Platform Memory Component
==============

Orchestra\Memory handles runtime configuration either using 'in memory' Runtime or database using Cache, Fluent Query Builder or Eloquent ORM. Instead of just allowing static configuration to be used, Orchestra\Memory allow those configuration to be persistent in between request by utilizing multiple data storage option either through cache or database.

[![Build Status](https://travis-ci.org/orchestral/memory.png?branch=master)](https://travis-ci.org/orchestral/memory) [![Coverage Status](https://coveralls.io/repos/orchestral/memory/badge.png?branch=master)](https://coveralls.io/r/orchestral/memory?branch=master)

## Quick Installation

To install through composer, simply put the following in your `composer.json` file:

```json
{
	"require": {
		"orchestra/memory": "2.0.*"
	},
	"minimum-stability": "dev"
}
```

Next add the service provider in `app/config/app.php`.

```php
'providers' => array(
	
	// ...
	
	'Orchestra\Memory\MemoryServiceProvider',
),
```

## Resources

* [Documentation](http://orchestraplatform.com/docs/2.0/components/memory)
* [Change Logs](https://github.com/orchestral/memory/wiki/Change-Logs)
