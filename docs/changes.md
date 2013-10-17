Memory Change Log
==============

## Version 2.0

### v2.0.4@dev

* Move commands to it's own service provider.

### v2.0.3

* Add `Orchestra\Memory\Abstractable\Container`.

### v2.0.2

* Code improvements.

### v2.0.1

* Minor docblock and code refactoring improvement.

### v2.0.0

* Migrate `Orchestra\Memory` from Orchestra Platform 1.2.
* Rename `Orchestra\Memory::shutdown()` to `Orchestra\Memory::finish()`.
* Add `Orchestra\Memory::makeOrFallback()` for easy usage to switch to `Orchestra\Memory\Drivers\Runtime` when database connection is not correct.
