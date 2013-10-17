Memory Component
==============

* [Installation](#installation)
* [Configuration](#configuration)

`Orchestra\Memory` handles runtime configuration either using "in memory" Runtime or database using Cache, Fluent Query Builder or Eloquent ORM. Instead of just allowing static configuration to be used, `Orchestra\Memory` allow those configuration to be persistent in between request by utilizing multiple data storage option either through cache or database.

## Installation

To install through composer, simply put the following in your `composer.json` file:

```json
{
	"require": {
		"orchestra/memory": "2.1.*@dev"
	}
}
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

### Migrations

Before we can start using `Orchestra\Memory`, please run the following:

```bash
php artisan orchestra:memory install
```

> The command utility is enabled via `Orchestra\Memory\CommandServiceProvider`.

### Publish Configuration

Optionally, you can also publish the configuration file if there any requirement to change the default:

```bash
php artisan config:publish --packages=orchestra/memory
```
