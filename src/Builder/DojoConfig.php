<?php

namespace Dojo\Builder;


use Zend\Stdlib\ArrayObject;

/**
 * Provides an interface to the dojoConfig object.
 * See https://dojotoolkit.org/documentation/tutorials/1.10/dojo_config/index.html
 *
 * @method $this setParseOnLoad(boolean $flag)
 * @method $this setAsync(string $flag) Sets the async flag can be true, false, legacyAsync
 * @method $this setWaitSeconds(int $seconds) Amount of time to wait before signaling load timeout for a module
 * @method $this cacheBust(boolean $flag) If true, appends the time as a querystring to each module URL to avoid module caching
 * @method $this baseUrl(string $url) The base URL prepended to a module identifier when converting it to a path or URL
 */
class DojoConfig extends ArrayObject
{
    /**
     * @var array[]
     */
    protected $packages = [];

    //region packages

    /**
     * Registers a new package that contains RequireJs Style Modules.
     *
     * If a package with this name is already registered, it will be overwritten.
     *
     * @param string $name
     * @param string|array $options The packages options, or the path to the module
     * @return $this
     */
    public function registerPackage($name, $options)
    {
        if (!is_array($options)) {
            $options = [
                'location' => $options
            ];
        } else {
            if (!isset($options['location'])) {
                throw new \InvalidArgumentException(sprintf(
                    'Options for package "%s" must contains a location entry'
                ), $name);
            }
        }

        if (!isset($options['name'])) {
            $options['name'] = $name;
        }

        //remove trailing slashes
        $options['location'] = rtrim($options['location'], '/');

        $this->packages[$name] = $options;

        return $this;
    }

    /**
     * Returns true if the package is registered.
     *
     * @param string $name
     * @return bool
     */
    public function isPackageRegistered($name)
    {
        return array_key_exists($name, $this->packages);
    }

    /**
     * Returns the details for the registered package.
     * Returns null if no package is registered
     *
     * @param $name
     * @return array|null
     */
    public function getPackage($name)
    {
        if (!isset($this->packages[$name])) {
            return null;
        }

        return $this->packages[$name];
    }

    /**
     * Gets the package configuration
     *
     * @return \array[]
     */
    public function getPackages()
    {
        return $this->packages;
    }
    //endregion

    /**
     * API to access the dojoConfig.has() configuration.
     *
     * If no argument is passed to value it will return the stored
     * value. Otherwise it will set the stored value.
     *
     * @param string $feature
     * @param mixed|null $value
     * @return $this
     */
    function has($feature)
    {
        if (!isset($this['has'])) {
            $this['has'] = [];
        }

        $args = func_get_args();

        if (count($args) == 1) {
            //Only a single argument was passed return the features value

            if (!isset($this['has'][$feature])) {
                return null;
            }

            return $this['has'][$feature];
        }

        //More than one argument was passed set the feature value

        $this['has'][$feature] = $args[1];

        return $this;
    }

    function __call($method, $args)
    {

        if (preg_match('/^(?P<action>set|get)(?P<property>.+)$/', $method, $matches)) {

            $property = lcfirst($matches['property']);

            switch ($matches['action']) {
                case 'get':
                    return $this[$property];

                case 'set':
                    if (count($args) != 1) {
                        throw new \BadMethodCallException(sprintf(
                            'Method "%s" requires at least one argument',
                            $method));
                    }

                    $this[$property] = $args[0];
                    return $this;
            }
        }

        throw new \BadMethodCallException(sprintf(
            'Invalid Method %s',
            $method));
    }
}