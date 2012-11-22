<?php
/**
 * @package Sds
 * @license MIT
 */
namespace Sds\DojoModule\View\Helper;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Sds\DojoModule\View\Helper\Dojo;

/**
 * Factory to Dojo View helper
 *
 * @author  Tim Roediger <superdweebie@gmail.com>
 * @author Dustin Thomson <dthomson@51systems.com>
 */
class DojoFactory implements FactoryInterface
{
    /**
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     * @return \Dojo\View\Helper\Dojo
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
//        $configService=$serviceLocator->getServiceLocator()->get('Configuration');
//        $config = $configService['sds']['dojo'];
        $instance = new Dojo();
//        $instance->setTheme($config['theme']);
//        $instance->setDojoRoot($config['dojoRoots'][$config['activeDojoRoot']]);
//        $instance->setRequires($config['require']);
//        $instance->setStylesheets($config['stylesheets']);
//        $instance->setLayer($config['layer']);
        return $instance;
    }
}