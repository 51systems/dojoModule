<?php

namespace Dojo\View\Helper;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * Factory to Dojo View helper
 *
 * @author  Tim Roediger <superdweebie@gmail.com>
 * @author Dustin Thomson <dthomson@51systems.com>
 */
class DojoFactory implements FactoryInterface
{
    /**
     * @inheritdoc
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new Dojo();
    }
}