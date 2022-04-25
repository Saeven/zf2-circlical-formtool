<?php

namespace Circlical\LaminasTools\Factory\Service;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\AbstractFactoryInterface;

class AbstractWriterFactory implements AbstractFactoryInterface
{
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        return preg_match('/^Circlical\\\\LaminasTools\\\\Service\\\\(.*?)Writer$/uix', $requestedName);
    }

    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $config = $container->get('config');

        return new $requestedName(
            $config['circlical']['powertools']['resource_templates']
        );
    }
}

