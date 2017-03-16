<?php

namespace Spatie\Blink;

use Countable;
use ArrayAccess;

class Blink implements ArrayAccess, Countable
{
    /** @var array */
    protected $values = [];

    /**
     * Put a value in the store.
     *
     * @param string|array $name
     * @param mixed $value
     *
     * @return $this
     */
    public function put($name, $value = null)
    {
        $newValues = $name;

        if (! is_array($name)) {
            $newValues = [$name => $value];
        }

        $this->values = array_merge($this->values, $newValues);

        return $this;
    }

    /**
     * Get a value from the store.
     *
     * @param string $name
     * @param mixed $default
     *
     * @return null|string
     */
    public function get(string $name, $default = null)
    {
        return $this->has($name)
            ? $this->values[$name]
            : $default;
    }

    /*
     * Determine if the store has a value for the given name.
     */
    public function has(string $name): bool
    {
        return array_key_exists($name, $this->values);
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
        if ($startingWith === '') {
            return $this->values;
        }

        return $this->filterKeysStartingWith($this->values, $startingWith);
    }

    /**
     * Forget a value from the store.
     *
     * @param string $key
     *
     * @return $this
     */
    public function forget(string $key)
    {
        unset($this->values[$key]);

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
     * Flush all values which keys start with a given string.
     *
     * @param string $startingWith
     *
     * @return $this
     */
    public function flushStartingWith(string $startingWith = '')
    {
        if ($startingWith !== '') {
            $this->values = $this->filterKeysNotStartingWith($this->values, $startingWith);
        }

        return $this;
    }

    /**
     * Get and forget a value from the store.
     *
     * @param string $name
     *
     * @return null|string
     */
    public function pull(string $name)
    {
        $value = $this->get($name);

        $this->forget($name);

        return $value;
    }

    /**
     * Increment a value from the store.
     *
     * @param string $name
     * @param int $by
     *
     * @return int|null|string
     */
    public function increment(string $name, int $by = 1)
    {
        $currentValue = $this->get($name) ?? 0;

        $newValue = $currentValue + $by;

        $this->put($name, $newValue);

        return $newValue;
    }

    /**
     * Decrement a value from the store.
     *
     * @param string $name
     * @param int $by
     *
     * @return int|null|string
     */
    public function decrement(string $name, int $by = 1)
    {
        return $this->increment($name, $by * -1);
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
    public function offsetExists($offset)
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
    public function offsetGet($offset)
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
    public function offsetSet($offset, $value)
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
    public function offsetUnset($offset)
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
    public function count()
    {
        return count($this->all());
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
}
