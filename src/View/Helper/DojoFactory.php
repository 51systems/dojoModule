<?php

namespace Dojo\View\Helper;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Dojo\View\Helper\Dojo;

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
     * @return Dojo
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new Dojo();
    }
}