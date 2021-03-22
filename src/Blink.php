<?php

namespace Spatie\Blink;

use ArrayAccess;
use Countable;
use Psr\SimpleCache\CacheInterface;

class Blink implements ArrayAccess, Countable, CacheInterface
{
    /** @var array */
    protected array $values = [];

    /**
     * Put a value in the store.
     *
     * @param string|array $key
     * @param mixed $value
     *
     * @return $this
     */
    public function put($key, $value = null): self
    {
        $newValues = $key;

        if (is_array($key)) {
            $this->values = $this->values + $newValues;
        } else {
            $this->values[$key] = $value;
        }

        return $this;
    }

    public function set($key, $value, $ttl = null): bool
    {
        $this->put($key, $value);

        return true;
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
    public function get($key, $default = null)
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
    public function has($key): bool
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
    public function forget(string $key): self
    {
        $this->delete($key);

        return $this;
    }

    public function delete($key): bool
    {
        $keys = $this->stringContainsWildcard($key)
            ? $this->getKeysMatching($key)
            : [$key];

        foreach ($keys as $key) {
            unset($this->values[$key]);
        }

        return true;
    }

    /**
     * Flush all values from the store.
     *
     * @return $this
     */
    public function flush(): self
    {
        $this->clear();

        return $this;
    }

    public function clear(): bool
    {
        $this->values = [];

        return true;
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

        $this->delete($key);

        return $value;
    }

    public function increment(string $key, int $by = 1): int
    {
        $currentValue = $this->get($key) ?? 0;

        $newValue = $currentValue + $by;

        $this->put($key, $newValue);

        return $newValue;
    }

    public function decrement(string $key, int $by = 1): int
    {
        return $this->increment($key, $by * -1);
    }

    public function offsetExists($offset): bool
    {
        return $this->has($offset);
    }

    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    public function offsetSet($offset, $value): void
    {
        $this->set($offset, $value);
    }

    public function offsetUnset($offset): void
    {
        $this->delete($offset);
    }

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

    public function getMultiple($keys, $default = null): array
    {
        $values = [];

        foreach($keys as $key) {
            $values[$key] =  $this->get($key, $default);
        }

        return $values;
    }

    public function setMultiple($values, $ttl = null): bool
    {
        foreach ($values as $key => $value) {
            $this->set($key, $value, $ttl);
        }

        return true;
    }

    public function deleteMultiple($keys): bool
    {
        foreach($keys as $key) {
            $this->delete($key);
        }

        return true;
    }
}
