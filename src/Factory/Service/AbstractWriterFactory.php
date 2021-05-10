<?php

namespace Circlical\LaminasTools\Factory\Service;

use Circlical\LaminasTools\Service\ControllerWriter;
use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\Factory\AbstractFactoryInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class AbstractWriterFactory implements AbstractFactoryInterface
{
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        return preg_match('/^Circlical\\\\LaminasTools\\\\Service\\\\(.*?)Writer$/uix', $requestedName);
    }

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config');

        return new $requestedName(
            $config['circlical']['powertools']['resource_templates']
        );
    }
}

