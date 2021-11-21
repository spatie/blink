# Cache that expires in the blink of an eye

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spatie/blink.svg?style=flat-square)](https://packagist.org/packages/spatie/blink)
![GitHub Workflow Status](https://img.shields.io/github/workflow/status/spatie/blink/run-tests?label=tests)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/blink.svg?style=flat-square)](https://packagist.org/packages/spatie/blink)

This package contains a class called `Blink` that can cache values. The cache only spans the length of a single request.

It can be used like this:

```php
$blink = new Blink();

$blink->put('key', 'value');

$blink->get('key'); // Returns 'value'
$blink->get('prefix*'); // Returns an array of values whose keys start with 'prefix'

// once will only execute the given callable if the given key didn't exist yet
$expensiveFunction = function() {
   return rand();
});
$blink->once('random', $expensiveFunction); // returns random number
$blink->once('random', $expensiveFunction); // returns the same number

$blink->has('key'); // Returns true
$blink->has('prefix*'); // Returns true if the blink contains a key that starts with 'prefix'

// Specify a default value for when the specified key does not exist
$blink->get('non existing key', 'default') // Returns 'default'

$blink->put('anotherKey', 'anotherValue');

// Put multiple items in one go
$blink->put(['ringo' => 'drums', 'paul' => 'bass']);

$blink->all(); // Returns an array with all items

$blink->forget('key'); // Removes the item
$blink->forget('prefix*'); // Forget all items of which the key starts with 'prefix'

$blink->flush(); // Empty the entire blink

$blink->flushStartingWith('somekey'); // Remove all items whose keys start with "somekey"

$blink->increment('number'); // $blink->get('number') will return 1
$blink->increment('number'); // $blink->get('number') will return 2
$blink->increment('number', 3); // $blink->get('number') will return 5

// Blink implements ArrayAccess
$blink['key'] = 'value';
$blink['key']; // Returns 'value'
isset($blink['key']); // Returns true
unset($blink['key']); // Equivalent to removing the value

// Blink implements Countable
count($blink); // Returns 0
$blink->put('key', 'value');
count($blink); // Returns 1
```

Read the [usage](#usage) section of this readme to learn the other methods.

## Support us

[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/blink.jpg?t=1" width="419px" />](https://spatie.be/github-ad-click/blink)

We invest a lot of resources into creating [best in class open source packages](https://spatie.be/open-source). You can support us by [buying one of our paid products](https://spatie.be/open-source/support-us).

We highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using. You'll find our address on [our contact page](https://spatie.be/about-us). We publish all received postcards on [our virtual postcard wall](https://spatie.be/open-source/postcards).

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

You can call the following methods on it:

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
 * This function has support for the '*' wildcard.
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
 *
 * This function has support for the '*' wildcard.
 */
public function has(string $name) : bool
```

### once

```php
/**
 * Only if the given key is not present in the blink cache the callable will be executed.
 *
 * The result of the callable will be stored in the given key and returned.
 *
 * @param $key
 * @param callable $callable
 *
 * @return mixed
 */
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
 * Get all values from the blink cache which keys start with the given string.
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
 * This function has support for the '*' wildcard.
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
 * Flush all values from the blink cache which keys start with the specified value.
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
 * This function has support for the '*' wildcard.
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

We got the idea and the name for this package from [Statamic's Blink helper](https://docs.statamic.com/addons/helpers#blink-cache). We reached out to them and got permission for using the `blink` name.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
