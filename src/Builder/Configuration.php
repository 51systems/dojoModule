<?php

namespace Dojo\Builder;

use Dojo\Constants;
use Zend\Json\Expr as JsonExpr;
use Zend\Json\Json;
use Dojo\View\Exception\InvalidArgumentException;
use Dojo\View\Exception\RuntimeException;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Renderer\RendererInterface;

/**
 * A dojo view helper. Based off original Zend Framework 1.x code and Tim Roediger's ZF2 Dojo Module.
 * @see http://framework.zend.com/manual/1.12/en/zend.dojo.view.html
 * @see https://github.com/superdweebie/dojoModule
 *
 * @author Dustin Thomson <dthomson@51systems.com>
 */
class Configuration
{
    /**
     * @var \Zend\View\Renderer\RendererInterface
     */
    public $view;

    /**
     * @var DojoConfig
     */
    protected $dojoConfig;

    /**
     * addOnLoad capture lock
     * @var bool
     */
    protected $_captureLock = false;

    /**
     * addOnLoad object on which to apply lambda
     * @var string
     */
    protected $_captureObj;

    /**
     * Base CDN url to utilize
     * @var string
     */
    protected $_cdnBase = Constants::CDN_BASE_GOOGLE;

    /**
     * Path segment following version string of CDN path
     * @var string
     */
    protected $_cdnDojoPath = Constants::CDN_DOJO_PATH_GOOGLE;

    /**
     * Dojo version to use from CDN
     * @var string
     */
    protected $_cdnVersion = Constants::CDN_VERSION;

    /**
     * Has the dijit loader been registered?
     * @var bool
     */
    protected $_dijitLoaderRegistered = false;

    /**
     * Registered programmatic dijits
     * @var array
     */
    protected $_dijits = array();

    /**
     * Whether or not dojo is enabled
     * @var bool
     */
    protected $_enabled = false;

    /**
     * Are we rendering as XHTML?
     * @var bool
     */
    protected $_isXhtml = false;

    /**
     * Arbitrary javascript to include in dojo script
     * @var array
     */
    protected $_javascriptStatements = array();

    /**
     * Dojo layers (custom builds) to use
     * @var array
     */
    protected $_layers = array();

    /**
     * Relative path to dojo
     * @var string
     */
    protected $_localPath = null;

    /**
     * Root of dojo where all dojo files are installed
     * @var string
     */
    protected $_localRelativePath = null;

    /**
     * Modules to require
     * @var string[]
     */
    protected $_modules = array();

    /**
     * Actions to perform on window load
     * @var array
     */
    protected $_onLoadActions = array();

    /**
     * Register the Dojo stylesheet?
     * @var bool
     */
    protected $_registerDojoStylesheet = false;

    /**
     * Local stylesheets
     * @var array
     */
    protected $_stylesheets = array();

    /**
     * Array of onLoad events specific to Dojo integration operations
     * @var array
     */
    protected $_zendLoadActions = array();

    /**
     * Flag to indicate if the declarative style of dojo should be used.
     *
     * @var boolean
     */
    protected $useDeclarative;

    public function __construct($useDeclarative)
    {
        $this->dojoConfig = new DojoConfig();
        $this->useDeclarative = $useDeclarative;
    }

    /**
     * Enable dojo
     *
     * @return Configuration
     */
    public function enable()
    {
        $this->_enabled = true;
        return $this;
    }

    /**
     * Disable dojo
     *
     * @return Configuration
     */
    public function disable()
    {
        $this->_enabled = false;
        return $this;
    }

    /**
     * Is dojo enabled?
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->_enabled;
    }

    public function setView(RendererInterface $view)
    {
        $this->view = $view;
    }

    /**
     * Add options for the Dojo Container to use
     *
     * @param array|\Zend\Config\Config Array or \Zend\Config\Config object with options to use
     * @return Configuration
     */
    public function setOptions($options)
    {
        if($options instanceof \Zend\Config\Config) {
            $options = $options->toArray();
        }

        foreach($options as $key => $value) {
            $key = strtolower($key);
            switch($key) {
                case 'requiremodules':
                    $this->requireModule($value);
                    break;
                case 'layers':
                    $value = (array) $value;
                    foreach($value as $layer) {
                        $this->addLayer($layer);
                    }
                    break;
                case 'cdnbase':
                    $this->setCdnBase($value);
                    break;
                case 'cdnversion':
                    $this->setCdnVersion($value);
                    break;
                case 'cdndojopath':
                    $this->setCdnDojoPath($value);
                    break;
                case 'localpath':
                    $this->setLocalPath($value);
                    break;
                case 'djconfig':
                    $this->setDjConfig($value);
                    break;
                case 'stylesheets':
                    $value = (array) $value;
                    foreach($value as $stylesheet) {
                        $this->addStylesheet($stylesheet);
                    }
                    break;
                case 'registerdojostylesheet':
                    $this->registerDojoStylesheet($value);
                    break;
                case 'enable':
                    if($value) {
                        $this->enable();
                    } else {
                        $this->disable();
                    }
            }
        }

        return $this;
    }

    /**
     * Specify one or multiple modules to require
     *
     * @param  string|array $modules
     * @return Configuration
     */
    public function requireModule($modules)
    {
        if (!is_string($modules) && !is_array($modules)) {
            throw new InvalidArgumentException('Invalid module name specified; must be a string or an array of strings');
        }

        $modules = (array) $modules;

        foreach ($modules as $mod) {
            if (!preg_match('%^[a-z][-._/a-z0-9]+$%i', $mod)) {
                throw new InvalidArgumentException(sprintf('Module name specified, "%s", contains invalid characters', (string) $mod));
            }

            if (!in_array($mod, $this->_modules)) {
                $this->_modules[] = $mod;
            }
        }

        return $this;
    }

    /**
     * Retrieve list of modules to require
     *
     * @return array
     */
    public function getModules()
    {
        return $this->_modules;
    }

    /**
     * Add layer (custom build) path
     *
     * @param  string $path
     * @return Configuration
     */
    public function addLayer($path)
    {
        $path = (string) $path;
        if (!in_array($path, $this->_layers)) {
            $this->_layers[] = $path;
        }

        return $this;
    }

    /**
     * Get registered layers
     *
     * @return array
     */
    public function getLayers()
    {
        return $this->_layers;
    }

    /**
     * Remove a registered layer
     *
     * @param  string $path
     * @return Configuration
     */
    public function removeLayer($path)
    {
        $path = (string) $path;
        $layers = array_flip($this->_layers);
        if (array_key_exists($path, $layers)) {
            unset($layers[$path]);
            $this->_layers = array_keys($layers);
        }
        return $this;
    }

    /**
     * Clear all registered layers
     *
     * @return Configuration
     */
    public function clearLayers()
    {
        $this->_layers = array();
        return $this;
    }

    /**
     * Set CDN base path
     *
     * @param  string $url
     * @return Configuration
     */
    public function setCdnBase($url)
    {
        $this->_cdnBase = (string) $url;
        return $this;
    }

    /**
     * Return CDN base URL
     *
     * @return string
     */
    public function getCdnBase()
    {
        return $this->_cdnBase;
    }

    /**
     * Use CDN, using version specified
     *
     * @param  string $version
     * @return Configuration
     */
    public function setCdnVersion($version = null)
    {
        $this->enable();
        if (preg_match('/^[1-9]\.[0-9](\.[0-9])?$/', $version)) {
            $this->_cdnVersion = $version;
        }
        return $this;
    }

    /**
     * Get CDN version
     *
     * @return string
     */
    public function getCdnVersion()
    {
        return $this->_cdnVersion;
    }

    /**
     * Set CDN path to dojo (relative to CDN base + version)
     *
     * @param  string $path
     * @return Configuration
     */
    public function setCdnDojoPath($path)
    {
        $this->_cdnDojoPath = (string) $path;
        return $this;
    }

    /**
     * Get CDN path to dojo (relative to CDN base + version)
     *
     * @return string
     */
    public function getCdnDojoPath()
    {
        return $this->_cdnDojoPath;
    }

    /**
     * Are we using the CDN?
     *
     * @return bool
     */
    public function useCdn()
    {
        return !$this->useLocalPath();
    }

    /**
     * Set path to local dojo
     *
     * @param  string $path
     * @return Configuration
     */
    public function setLocalPath($path)
    {
        $this->enable();
        $this->_localPath = (string) $path;
        return $this;
    }

    /**
     * Get local path to dojo
     *
     * @return string
     */
    public function getLocalPath()
    {
        return $this->_localPath;
    }

    /**
     * Are we using a local path?
     *
     * @return bool
     */
    public function useLocalPath()
    {
        return (null === $this->_localPath) ? false : true;
    }

    /**
     * Retrieve dojo configuration values
     *
     * @return DojoConfig
     */
    public function getDojoConfig()
    {
        return $this->dojoConfig;
    }

    /**
     * Add a stylesheet.
     *
     * @param  string $path The path to the sylesheet. Any instances of string @dojoPath will be replaced with the dojo path
     * @return Configuration
     */
    public function addStylesheet($path)
    {
        $path = (string) $path;
        if (!in_array($path, $this->_stylesheets)) {
            $this->_stylesheets[] = (string) $path;
        }
        return $this;
    }

    /**
     * Register the dojo.css stylesheet?
     *
     * With no arguments, returns the status of the flag; with arguments, sets
     * the flag and returns the object.
     *
     * @param  null|bool $flag
     * @return Configuration|bool
     */
    public function registerDojoStylesheet($flag = null)
    {
        if (null === $flag) {
            return $this->_registerDojoStylesheet;
        }

        $this->_registerDojoStylesheet = (bool) $flag;
        return $this;
    }

    /**
     * Retrieve registered stylesheets
     *
     * @return array
     */
    public function getStylesheets()
    {
        return $this->_stylesheets;
    }

    /**
     * Add a script to execute onLoad
     *
     * dojo.addOnLoad accepts:
     * - function name
     * - lambda
     *
     * @param  string $callback Lambda
     * @return Configuration
     */
    public function addOnLoad($callback)
    {
        if (!in_array($callback, $this->_onLoadActions, true)) {
            $this->_onLoadActions[] = $callback;
        }
        return $this;
    }

    /**
     * Prepend an onLoad event to the list of onLoad actions
     *
     * @param  string $callback Lambda
     * @return Configuration
     */
    public function prependOnLoad($callback)
    {
        if (!in_array($callback, $this->_onLoadActions, true)) {
            array_unshift($this->_onLoadActions, $callback);
        }
        return $this;
    }

    /**
     * Retrieve all registered onLoad actions
     *
     * @return array
     */
    public function getOnLoadActions()
    {
        return $this->_onLoadActions;
    }

    /**
     * Start capturing routines to run onLoad
     *
     * @return bool
     */
    public function onLoadCaptureStart()
    {
        if ($this->_captureLock) {
            throw new InvalidArgumentException('Cannot nest onLoad captures');
        }

        $this->_captureLock = true;
        ob_start();
        return;
    }

    /**
     * Stop capturing routines to run onLoad
     *
     * @return bool
     */
    public function onLoadCaptureEnd()
    {
        $data               = ob_get_clean();
        $this->_captureLock = false;

        $this->addOnLoad($data);
        return true;
    }

    /**
     * Add a programmatic dijit
     *
     * @param  string $id
     * @param  array $params
     * @return Configuration
     */
    public function addDijit($id, array $params)
    {
        if (array_key_exists($id, $this->_dijits)) {
            throw new RuntimeException(sprintf('Duplicate dijit with id "%s" already registered', $id));
        }

        $this->_dijits[$id] = array(
            'id'     => $id,
            'params' => $params,
        );

        return $this;
    }

    /**
     * Set a programmatic dijit (overwrites)
     *
     * @param  string $id
     * @param  array $params
     * @return Configuration
     */
    public function setDijit($id, array $params)
    {
        $this->removeDijit($id);
        return $this->addDijit($id, $params);
    }

    /**
     * Add multiple dijits at once
     *
     * Expects an array of id => array $params pairs
     *
     * @param  array $dijits
     * @return Configuration
     */
    public function addDijits(array $dijits)
    {
        foreach ($dijits as $id => $params) {
            $this->addDijit($id, $params);
        }
        return $this;
    }

    /**
     * Set multiple dijits at once (overwrites)
     *
     * Expects an array of id => array $params pairs
     *
     * @param  array $dijits
     * @return Configuration
     */
    public function setDijits(array $dijits)
    {
        $this->clearDijits();
        return $this->addDijits($dijits);
    }

    /**
     * Is the given programmatic dijit already registered?
     *
     * @param  string $id
     * @return bool
     */
    public function hasDijit($id)
    {
        return array_key_exists($id, $this->_dijits);
    }

    /**
     * Retrieve a dijit by id
     *
     * @param  string $id
     * @return array|null
     */
    public function getDijit($id)
    {
        if ($this->hasDijit($id)) {
            return $this->_dijits[$id]['params'];
        }
        return null;
    }

    /**
     * Retrieve all dijits
     *
     * Returns dijits as an array of assoc arrays
     *
     * @return array
     */
    public function getDijits()
    {
        return array_values($this->_dijits);
    }

    /**
     * Remove a programmatic dijit if it exists
     *
     * @param  string $id
     * @return Configuration
     */
    public function removeDijit($id)
    {
        if (array_key_exists($id, $this->_dijits)) {
            unset($this->_dijits[$id]);
        }

        return $this;
    }

    /**
     * Clear all dijits
     *
     * @return Configuration
     */
    public function clearDijits()
    {
        $this->_dijits = array();
        return $this;
    }

    /**
     * Render dijits as JSON structure
     *
     * @return string
     */
    public function dijitsToJson()
    {
        return Json::encode($this->getDijits(), false, array('enableJsonExprFinder' => true));
    }

    /**
     * Create dijit loader functionality
     *
     * @return void
     */
    public function registerDijitLoader()
    {
        if (!$this->_dijitLoaderRegistered) {
            $js =<<<EOJ
function() {
    require(["dojo/_base/lang", "dojo/_base/array", "dojo/dom", "dojo/dom-attr", "dojo/parser"], function(lang, array, dom, attr, parser) {
            array.forEach(zendDijits, function(info) {
                var n = dom.byId(info.id);
                if (null != n) {
                    attr.set(n, lang.mixin({ id: info.id }, info.params));
                }
            });
            parser.parse();
        }
    );
}
EOJ;
            $this->_addZendLoad($js);
            $this->addJavascript('var zendDijits = ' . $this->dijitsToJson() . ';');
            $this->_dijitLoaderRegistered = true;
        }
    }

    /**
     * Add arbitrary javascript to execute in dojo JS container
     *
     * @param  string $js
     * @return Configuration
     */
    public function addJavascript($js)
    {
        $js = trim($js);
        if (!in_array(substr($js, -1), array(';', '}'))) {
            $js .= ';';
        }

        if (in_array($js, $this->_javascriptStatements)) {
            return $this;
        }

        $this->_javascriptStatements[] = $js;
        return $this;
    }

    /**
     * Return all registered javascript statements
     *
     * @return array
     */
    public function getJavascript()
    {
        return $this->_javascriptStatements;
    }

    /**
     * Clear arbitrary javascript stack
     *
     * @return Configuration
     */
    public function clearJavascript()
    {
        $this->_javascriptStatements = array();
        return $this;
    }

    /**
     * Capture arbitrary javascript to include in dojo script
     *
     * @return void
     */
    public function javascriptCaptureStart()
    {
        if ($this->_captureLock) {
            throw new RuntimeException('Cannot nest captures');
        }

        $this->_captureLock = true;
        ob_start();
        return;
    }

    /**
     * Finish capturing arbitrary javascript to include in dojo script
     *
     * @return true
     */
    public function javascriptCaptureEnd()
    {
        $data               = ob_get_clean();
        $this->_captureLock = false;

        $this->addJavascript($data);
        return true;
    }

    /**
     * String representation of dojo environment
     *
     * @return string
     */
    public function __toString()
    {
        if (!$this->isEnabled()) {
            return '';
        }

        if ($this->view instanceof PhpRenderer) {
            $this->_isXhtml = $this->view->doctype()->isXhtml();
        } else {
            $this->_isXhtml = false;
        }

        if ($this->useDeclarative) {
            $this->getDojoConfig()->setParseOnLoad(true);
        }

        if (!empty($this->_dijits)) {
            $this->registerDijitLoader();
        }

        $html  = $this->_renderStylesheets() . PHP_EOL
            . $this->_renderDojoConfig() . PHP_EOL
            . $this->_renderDojoScriptTag() . PHP_EOL
            . $this->_renderLayers() . PHP_EOL
            . $this->_renderExtras();
        return $html;
    }

    /**
     * Retrieve local path to dojo resources for building relative paths
     *
     * @return string
     */
    protected function _getLocalRelativePath()
    {
        if (null === $this->_localRelativePath) {
            $localPath = $this->getLocalPath();
            $localPath = preg_replace('|[/\\\\]dojo[/\\\\]dojo.js[^/\\\\]*$|i', '', $localPath);
            $this->_localRelativePath = $localPath;
        }
        return $this->_localRelativePath;
    }

    /**
     * Render dojo stylesheets
     *
     * @return string
     */
    protected function _renderStylesheets()
    {
        if ($this->useCdn()) {
            $base = $this->getCdnBase()
                . $this->getCdnVersion();
        } else {
            $base = $this->_getLocalRelativePath();
        }


        $packages = $this->getDojoConfig()->getPackages();

        if (!isset($packages['dojo'])) {
            $packages['dojo'] = ['location' => $base];
        }

        if (!isset($packages['dijit'])) {
            $packages['dijit'] = ['location' => $base . '/dijit'];
        }

        if (!isset($packages['dojox'])) {
            $packages['dojox'] = ['location' => $base . '/dojox'];
        }

        foreach ($this->getStylesheets() as $stylesheet) {

            //Check to see if the stylesheet is prefixed by any of the
            //package names. If so, replace the prefix by the package path.
            if (($pos = stripos($stylesheet, '/') !== false)) {
                $prefix = substr($stylesheet, 0, $pos);

                if (isset($packages[$prefix])) {
                    $stylesheet = $packages['location'] . '/' . substr($stylesheet, strlen($prefix));
                }
            }

            $stylesheets[] = $stylesheet;
        }

        if ($this->_registerDojoStylesheet) {
            $stylesheets[] = $base . '/dojo/resources/dojo.css';
        }

        if (empty($stylesheets)) {
            return '';
        }

        array_reverse($stylesheets);
        $style = '<style type="text/css">' . PHP_EOL
            . (($this->_isXhtml) ? '<!--' : '<!--') . PHP_EOL;
        foreach ($stylesheets as $stylesheet) {
            $style .= '    @import "' . $stylesheet . '";' . PHP_EOL;
        }
        $style .= (($this->_isXhtml) ? '-->' : '-->') . PHP_EOL
            . '</style>';

        return $style;
    }

    /**
     * Render DjConfig values
     *
     * @return string
     */
    protected function _renderDojoConfig()
    {
        $config = $this->getDojoConfig();

        $configArray = [];

        $packages = $config->getPackages();
        if (!empty($packages)) {
            //If we don't grab array_values, an object is output
            $configArray['packages'] = $packages;
        }

        //Require dependencies
        $modules = array_filter($this->getModules(), function($moduleName) {
            return strpos($moduleName, '/') !== false;
        });

        if (!empty($modules)) {
            //If we don't grab array_values, an object is output
            $configArray['deps'] = array_values($modules);
        }


        if (empty($configArray)) {
            return '';
        }

        $scriptTag = '<script type="text/javascript">' . PHP_EOL
            . (($this->_isXhtml) ? '//<![CDATA[' : '//<!--') . PHP_EOL
            . '    var dojoConfig = ' . Json::encode($configArray) . ';' . PHP_EOL
            . (($this->_isXhtml) ? '//]]>' : '//-->') . PHP_EOL
            . '</script>';

        return $scriptTag;
    }

    /**
     * Render dojo script tag
     *
     * Renders Dojo script tag by utilizing either local path provided or the
     * CDN. If any djConfig values were set, they will be serialized and passed
     * with that attribute.
     *
     * @return string
     */
    protected function _renderDojoScriptTag()
    {
        if ($this->useCdn()) {
            $source = $this->getCdnBase()
                . $this->getCdnVersion()
                . $this->getCdnDojoPath();
        } else {
            $source = $this->getLocalPath();
        }

        $scriptTag = '<script type="text/javascript" src="' . $source . '"></script>';
        return $scriptTag;
    }

    /**
     * Render layers (custom builds) as script tags
     *
     * @return string
     */
    protected function _renderLayers()
    {
        $layers = $this->getLayers();
        if (empty($layers)) {
            return '';
        }

        $enc = 'UTF-8';
        if (method_exists($this->view, 'getEncoding')
        ) {
            $enc = $this->view->getEncoding();
        }

        $html = array();
        foreach ($layers as $path) {
            $html[] = sprintf(
                '<script type="text/javascript" src="%s"></script>',
                htmlspecialchars($path, ENT_QUOTES, $enc)
            );
        }

        return implode("\n", $html);
    }

    /**
     * Render dojo module paths and requires
     *
     * @return string
     */
    protected function _renderExtras()
    {
        $js = array();

        $onLoadActions = array();
        // Get Zend specific onLoad actions; these will always be first to
        // ensure that dijits are created in the correct order
        foreach ($this->_getZendLoadActions() as $callback) {
            $onLoadActions[] = 'ready(' . $callback . ');';
        }

        // Get all other onLoad actions
        foreach ($this->getOnLoadActions() as $callback) {
            $onLoadActions[] = 'ready(' . $callback . ');';
        }

        $javascript = implode("\n    ", $this->getJavascript());

        $content = '';
        if (!empty($js)) {
            $content .= implode("\n    ", $js) . "\n";
        }

        if (!empty($onLoadActions)) {
            $content .= 'require(["dojo/ready"], function (ready) {';
            $content .= implode("\n    ", $onLoadActions) . "\n";
            $content .= "});";
        }

        if (!empty($javascript)) {
            $content .= $javascript . "\n";
        }

        if (preg_match('/^\s*$/s', $content)) {
            return '';
        }

        $html = '<script type="text/javascript">' . PHP_EOL
            . (($this->_isXhtml) ? '//<![CDATA[' : '//<!--') . PHP_EOL
            . $content
            . (($this->_isXhtml) ? '//]]>' : '//-->') . PHP_EOL
            . PHP_EOL . '</script>';
        return $html;
    }

    /**
     * Add an onLoad action related to ZF dijit creation
     *
     * This method is public, but prefixed with an underscore to indicate that
     * it should not normally be called by userland code. It is pertinent to
     * ensuring that the correct order of operations occurs during dijit
     * creation.
     *
     * @param  string $callback
     * @return Configuration
     */
    public function _addZendLoad($callback)
    {
        if (!in_array($callback, $this->_zendLoadActions, true)) {
            $this->_zendLoadActions[] = $callback;
        }
        return $this;
    }

    /**
     * Retrieve all ZF dijit callbacks
     *
     * @return array
     */
    public function _getZendLoadActions()
    {
        return $this->_zendLoadActions;
    }
}
