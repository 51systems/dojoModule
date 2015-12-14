<?php

namespace Dojo\View\Helper;

use Dojo\Builder\Configuration;
use Zend\View\Helper\AbstractHelper;
use Zend\View\Renderer\RendererInterface as Renderer;

/**
 *
 * @method void enable() Enables dojo
 * @method Configuration requireModule(string $modules)
 * @method Configuration setDijit(string $id, array $params) Set a programmatic dijit (overwrites)
 * @method Configuration addJavascript(string $js) Add arbitrary javascript to execute in dojo JS container
 */
class Dojo extends AbstractHelper
{
    /**#@+
     * Programmatic dijit creation style constants
     */
    const PROGRAMMATIC_SCRIPT = 1;
    const PROGRAMMATIC_NOSCRIPT = -1;
    /**#@-*/

    /**
     * @var Zend_View_Interface
     */
    public $view;

    /**
     * @var Configuration
     */
    protected $_container;

    /**
     * @var bool Whether or not dijits should be declared programmatically
     */
    protected static $_useProgrammatic = true;

    /**
     * Initialize helper
     *
     * Retrieve container from registry or create new container and store in
     * registry.
     */
    public function __construct()
    {
        $this->_container = new Configuration(self::useDeclarative());
    }

    public function setView(Renderer $view)
    {
        $this->_container->setView($view);
        return parent::setView($view);
    }


    /**
     * Return dojo container
     *
     * @return Configuration
     */
    public function dojo()
    {
        return $this->_container;
    }

    /**
     * Proxy to container methods
     *
     * @param  string $method
     * @param  array $args
     * @return mixed
     * @throws \Dojo\View\Exception\RuntimeException For invalid method calls
     */
    public function __call($method, $args)
    {
        if (!method_exists($this->_container, $method)) {
            throw new \Dojo\View\Exception\RuntimeException(sprintf('Invalid method "%s" called on dojo view helper', $method));
        }

        return call_user_func_array(array($this->_container, $method), $args);
    }

    /**
     * Set whether or not dijits should be created declaratively
     *
     * @return void
     */
    public static function setUseDeclarative()
    {
        self::$_useProgrammatic = false;
    }

    /**
     * Set whether or not dijits should be created programmatically
     *
     * Optionally, specifiy whether or not dijit helpers should generate the
     * programmatic dojo.
     *
     * @param  int $style
     * @return void
     */
    public static function setUseProgrammatic($style = self::PROGRAMMATIC_SCRIPT)
    {
        if (!in_array($style, array(self::PROGRAMMATIC_SCRIPT, self::PROGRAMMATIC_NOSCRIPT))) {
            $style = self::PROGRAMMATIC_SCRIPT;
        }
        self::$_useProgrammatic = $style;
    }

    /**
     * Should dijits be created declaratively?
     *
     * @return bool
     */
    public static function useDeclarative()
    {
        return (false === self::$_useProgrammatic);
    }

    /**
     * Should dijits be created programmatically?
     *
     * @return bool
     */
    public static function useProgrammatic()
    {
        return (false !== self::$_useProgrammatic);
    }

    /**
     * Should dijits be created programmatically but without scripts?
     *
     * @return bool
     */
    public static function useProgrammaticNoScript()
    {
        return (self::PROGRAMMATIC_NOSCRIPT === self::$_useProgrammatic);
    }

    function __toString()
    {
        return $this->_container->__toString();
    }


}