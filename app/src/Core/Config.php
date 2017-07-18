<?php

namespace Morrison\Core;

/**
 * Simple config container with merge support
 */
class Config implements \ArrayAccess
{
    /**
     * The current config storage container.
     *
     * @var array
     */
    private $container;

    public function __construct($initial = array())
    {
        $this->container = $initial;
    }

    public function merge(array $input)
    {
        $this->container = array_merge($this->container, $input);
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            throw new \InvalidArgumentException('Null offsets not supported.');
        } else {
            $this->container[$offset] = $value;
        }
    }

    public function offsetExists($offset)
    {
        return isset($this->container[$offset]);
    }

    public function offsetUnset($offset)
    {
        unset($this->container[$offset]);
    }

    public function offsetGet($offset)
    {
        if (!isset($this->container[$offset])) {
            throw new \RuntimeException(sprintf('The %s config value is not set.', $offset));
        }

        return $this->container[$offset];
    }

    /**
     * Handy method to get the a time zone object for the
     * current ESP configuration
     *
     * @return \DateTimeZone Currently configured time zone object
     */
    public function getTimeZone()
    {
        return new \DateTimeZone($this->offsetGet('timezone'));
    }

    /**
     * Memcached Server list/array needs to be in a specific format ini files do not support
     * @return array
     */
    public function parseMemcachedPool()
    {
        if (!isset($this->container['memcached.pool']) || !is_array($this->container['memcached.pool'])) {
            throw new \RuntimeException('Key memcached.pool must be defined and an array in the ini files.');
        }

        $servers = array();

        foreach ($this->container['memcached.pool'] as $serverString) {
            $serverParts = explode(',', $serverString);

            if (count($serverParts) !== 3) {
                throw new \RuntimeException("$serverString is not a valid memcached server string");
            }

            $servers[] = $serverParts;
        }

        return $servers;
    }
}
