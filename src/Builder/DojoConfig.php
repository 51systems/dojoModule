<?php

namespace Dojo\Builder;


use Zend\Stdlib\ArrayObject;

class DojoConfig extends ArrayObject
{
    /**
     * @var array[]
     */
    protected $packages = [];


    /**
     * Registers a new package that contains RequireJs Style Modules.
     *
     * If a package with this name is already registered, it will be overwritten.
     *
     * @param string $name
     * @param string|array $options The packages options, or the path to the module
     * @return $this
     */
    protected function registerPackage($name, $options)
    {
        if (!is_array($options)) {
            $options = [
                'path' => $options
            ];
        }

        if (!isset($options['name'])) {
            $options['name'] = $name;
        }

        $this->packages[$name] = $name;

        return $this;
    }

    /**
     * Returns true if the package is registered.
     *
     * @param string $name
     * @return bool
     */
    protected function isPackageRegistered($name)
    {
        return array_key_exists($name, $this->packages);
    }

    function __call($name, $arguments)
    {
        

        return $this;
    }
}