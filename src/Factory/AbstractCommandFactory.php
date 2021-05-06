<?php

declare(strict_types=1);

namespace Circlical\LaminasTools\Factory;

use Circlical\LaminasTools\Command\CreateControllerCommand;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\AbstractFactoryInterface;

final class AbstractCommandFactory implements AbstractFactoryInterface
{
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        return strpos($requestedName, 'Circlical\LaminasTools\Command') !== false;
    }

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config');

        return new $requestedName($config['circlical']['powertools']);
    }
}