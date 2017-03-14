# Cache that expires in the blink of an eye

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spatie/blink.svg?style=flat-square)](https://packagist.org/packages/spatie/blink)
[![Build Status](https://img.shields.io/travis/spatie/blink/master.svg?style=flat-square)](https://travis-ci.org/spatie/blink)
[![SensioLabsInsight](https://img.shields.io/sensiolabs/i/2ce5c803-2126-4c73-8009-d65ff9d87d71.svg?style=flat-square)](https://insight.sensiolabs.com/projects/2ce5c803-2126-4c73-8009-d65ff9d87d71)
[![Quality Score](https://img.shields.io/scrutinizer/g/spatie/blink.svg?style=flat-square)](https://scrutinizer-ci.com/g/spatie/blink)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/blink.svg?style=flat-square)](https://packagist.org/packages/spatie/blink)

This package contains a class called `Blink` that can cache values. The cache only spans the lenght of a single request.

It can be used like this:

```php
$blink = new Blink();

$valuestore->put('key', 'value');

$valuestore->get('key'); // Returns 'value'

$valuestore->has('key'); // Returns true

// Specify a default value for when the specified key does not exist
$valuestore->get('non existing key', 'default') // Returns 'default'

$valuestore->put('anotherKey', 'anotherValue');

// Put multiple items in one go
$valuestore->put(['ringo' => 'drums', 'paul' => 'bass']);

$valuestore->all(); // Returns an array with all items

$valuestore->forget('key'); // Removes the item

$valuestore->flush(); // Empty the entire valuestore

$valuestore->flushStartingWith('somekey'); // remove all items who's keys start with "somekey"

$valuestore->increment('number'); // $valuestore->get('key') will return 1 
$valuestore->increment('number'); // $valuestore->get('key') will return 2
$valuestore->increment('number', 3); // $valuestore->get('key') will return 5

// Valuestore implements ArrayAccess
$valuestore['key'] = 'value';
$valuestore['key']; // Returns 'value'
isset($valuestore['key']); // Return true
unset($valuestore['key']); // Equivalent to removing the value

// Valuestore impements Countable
count($valuestore); // Returns 0
$valuestore->put('key', 'value');
count($valuestore); // Returns 1
```

Read the [usage](#usage) section of this readme to learn the other methods.

## Postcardware

You're free to use this package (it's [MIT-licensed](LICENSE.md)), but if it makes it to your production environment we highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using.

Our address is: Spatie, Samberstraat 69D, 2060 Antwerp, Belgium.

We publish all received postcards [on our company website](https://spatie.be/en/opensource/postcards).

## Installation

You can install the package via composer:

``` bash
composer require spatie/blink
```

## Usage

A `Blink` instance can just be newed up.

```php
$blink = new \Spatie\Blink\Blink()
```

You can call the following methods on it

### put
```php
/**
 * Put a value in the blink cache.
 *
 * @param string|array $name
 * @param string|int|null $value
 * 
 * @return $this
 */
public function put($name, $value = null)
```

### get

```php
/**
 * Get a value from the blink cache.
 *
 * @param string $name
 *
 * @return null|string
 */
public function get(string $name)
```

### has

```php
/*
 * Determine if the blink cache has a value for the given name.
 */
public function has(string $name) : bool
```

### all
```php
/*
 * Get all values in the blink cache.
*/
public function all() : array
```

### allStartingWith
```php
/**
 * Get all values in the blink cache which keys start with the given string.
 *
 * @param string $startingWith
 *
 * @return array
*/
public function allStartingWith(string $startingWith = '') : array
```

### forget
```php
/**
 * Forget a value from the blink cache.
 *
 * @param string $key
 *
 * @return $this
 */
public function forget(string $key)
```

### flush
```php
/**
 * Flush all values from the blink cache.
 *
 * @return $this
 */
 public function flush()
```

### flushStartingWith
```php
/**
 * Flush all values in the blink cache which keys start with the specified value.
 *
 * @param string $startingWith
 *
 * @return $this
 */
 public function flushStartingWith(string $startingWith)
```

### pull
```php
/**
 * Get and forget a value from the blink cache.
 *
 * @param string $name 
 *
 * @return null|string
 */
public function pull(string $name)
```

### increment
```php
/**
 * Increment a value from the blink cache.
 *
 * @param string $name
 * @param int $by
 *
 * @return int|null|string
 */
 public function increment(string $name, int $by = 1)
```

### decrement
```php
/**
 * Decrement a value from the blink cache.
 *
 * @param string $name
 * @param int $by
 *
 * @return int|null|string
 */
 public function decrement(string $name, int $by = 1)
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email freek@spatie.be instead of using the issue tracker.

## Credits

- [Freek Van der Herten](https://github.com/freekmurze)
- [All Contributors](../../contributors)

## About Spatie

Spatie is a webdesign agency based in Antwerp, Belgium. You'll find an overview of all our open source projects [on our website](https://spatie.be/opensource).

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
