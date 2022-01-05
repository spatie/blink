<?php

namespace Spatie\Blink;

use ArrayAccess;
use Countable;

class Blink implements ArrayAccess, Countable
{
    /** @var array */
    protected $values = [];

    /**
     * Put a value in the store.
     *
     * @param string|array $key
     * @param mixed $value
     *
     * @return $this
     */
    public function put($key, $value = null)
    {
        $newValues = $key;

        if (is_array($key)) {
            $this->values = $this->values + $newValues;
        } else {
            $this->values[$key] = $value;
        }

        return $this;
    }

    /**
     * Get a value from the store.
     *
     * This function has support for the '*' wildcard.
     *
     * @param string $key
     * @param mixed $default
     *
     * @return null|string|array
     */
    public function get(string $key, $default = null)
    {
        if ($this->stringContainsWildcard($key)) {
            $values = $this->getValuesForKeys($this->getKeysMatching($key));

            return count($values) ? $values : $default;
        }

        return $this->has($key)
            ? $this->values[$key]
            : $default;
    }

    /*
     * Determine if the store has a value for the given name.
     *
     * This function has support for the '*' wildcard.
     */
    public function has(string $key): bool
    {
        if ($this->stringContainsWildcard($key)) {
            return count($this->getKeysMatching($key)) > 0;
        }

        return array_key_exists($key, $this->values);
    }

    /**
     * Get all values from the store.
     *
     * @return array
     */
    public function all(): array
    {
        return $this->values;
    }

    /**
     * Get all keys starting with a given string from the store.
     *
     * @param string $startingWith
     *
     * @return array
     */
    public function allStartingWith(string $startingWith = ''): array
    {
        $values = $this->all();

        if ($startingWith === '') {
            return $values;
        }

        return $this->filterKeysStartingWith($values, $startingWith);
    }

    /**
     * Forget a value from the store.
     *
     * This function has support for the '*' wildcard.
     *
     * @param string $key
     *
     * @return $this
     */
    public function forget(string $key)
    {
        $keys = $this->stringContainsWildcard($key)
            ? $this->getKeysMatching($key)
            : [$key];

        foreach ($keys as $key) {
            unset($this->values[$key]);
        }

        return $this;
    }

    /**
     * Flush all values from the store.
     *
     * @return $this
     */
    public function flush()
    {
        return $this->values = [];
    }

    /**
     * Flush all values from the store which keys start with a given string.
     *
     * @param string $startingWith
     *
     * @return $this
     */
    public function flushStartingWith(string $startingWith = '')
    {
        $newContent = [];

        if ($startingWith !== '') {
            $newContent = $this->filterKeysNotStartingWith($this->all(), $startingWith);
        }

        $this->values = $newContent;

        return $this;
    }

    /**
     * Get and forget a value from the store.
     *
     * This function has support for the '*' wildcard.
     *
     * @param string $key
     *
     * @return null|string
     */
    public function pull(string $key)
    {
        $value = $this->get($key);

        $this->forget($key);

        return $value;
    }

    /**
     * Increment a value from the store.
     *
     * @param string $key
     * @param int $by
     *
     * @return int|null|string
     */
    public function increment(string $key, int $by = 1)
    {
        $currentValue = $this->get($key) ?? 0;

        $newValue = $currentValue + $by;

        $this->put($key, $newValue);

        return $newValue;
    }

    /**
     * Decrement a value from the store.
     *
     * @param string $key
     * @param int $by
     *
     * @return int|null|string
     */
    public function decrement(string $key, int $by = 1)
    {
        return $this->increment($key, $by * -1);
    }

    /**
     * Whether a offset exists.
     *
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param mixed $offset
     *
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return $this->has($offset);
    }

    /**
     * Offset to retrieve.
     *
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     *
     * @param mixed $offset
     *
     * @return mixed
     */
    public function offsetGet($offset): mixed
    {
        return $this->get($offset);
    }

    /**
     * Offset to set.
     *
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value): void
    {
        $this->put($offset, $value);
    }

    /**
     * Offset to unset.
     *
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param mixed $offset
     */
    public function offsetUnset($offset): void
    {
        $this->forget($offset);
    }

    /**
     * Count elements.
     *
     * @link http://php.net/manual/en/countable.count.php
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->all());
    }

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
    public function once($key, callable $callable)
    {
        if (! $this->has($key)) {
            $this->put($key, $callable());
        }

        return $this->get($key);
    }

    protected function filterKeysStartingWith(array $values, string $startsWith): array
    {
        return array_filter($values, function ($key) use ($startsWith) {
            return $this->startsWith($key, $startsWith);
        }, ARRAY_FILTER_USE_KEY);
    }

    protected function filterKeysNotStartingWith(array $values, string $startsWith): array
    {
        return array_filter($values, function ($key) use ($startsWith) {
            return ! $this->startsWith($key, $startsWith);
        }, ARRAY_FILTER_USE_KEY);
    }

    protected function startsWith(string $haystack, string $needle): bool
    {
        return substr($haystack, 0, strlen($needle)) === $needle;
    }

    protected function getKeysMatching(string $pattern): array
    {
        $keys = array_keys($this->values);

        return array_filter($keys, function ($key) use ($pattern) {
            return fnmatch($pattern, $key, FNM_NOESCAPE);
        });
    }

    protected function stringContainsWildcard(string $string): bool
    {
        return $this->stringContains($string, '*');
    }

    /**
     * @param string $haystack
     * @param string|array $needles
     *
     * @return bool
     */
    protected function stringContains(string $haystack, $needles): bool
    {
        foreach ((array) $needles as $needle) {
            if ($needle != '' && mb_strpos($haystack, $needle) !== false) {
                return true;
            }
        }

        return false;
    }

    public function getValuesForKeys(array $keys): array
    {
        return array_filter($this->values, function ($key) use ($keys) {
            return in_array($key, $keys);
        }, ARRAY_FILTER_USE_KEY);
    }
}
