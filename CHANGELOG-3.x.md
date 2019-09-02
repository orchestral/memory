# Changelog for 3.x

This changelog references the relevant changes (bug and security fixes) done to `orchestra/memory`.

## 3.8.0

Released: 2019-03-02

### Changes

* Update support for Laravel Framework v5.8.

### Removed

* Remove deprecated `Orchestra\Memory\CommandServiceProvider`.

## 3.7.1

Released: 2019-02-21

### Changes

* Improve performance by prefixing all global functions calls with `\` to skip the look up and resolve process and go straight to the global function.

## 3.7.0

Released: 2018-09-14

### Changes

* Update support for Laravel Framework v5.7.
* Disable `allowed_classes` when doing `unserialize()`. 

## 3.6.1

### Changes

* return `self` should only be used when method is marked as `final`.

### Deprecated

* Deprecate `Orchestra\Memory\CommandServiceProvider`.

## 3.6.0

Released: 2018-02-20

### Changes

* Update support for Laravel Framework v5.6.
