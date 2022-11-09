<?php

declare(strict_types=1);

namespace Circlical\LaminasTools\Factory\Command;

use Laminas\ServiceManager\Factory\AbstractFactoryInterface;
use Psr\Container\ContainerInterface;

use function strpos;

final class AbstractCommandFactory implements AbstractFactoryInterface
{
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        return false !== strpos($requestedName, 'Circlical\LaminasTools\Command');
    }

    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $config = $container->get('config');

        return new $requestedName(
            $config['circlical']['powertools'],
            $container->get($requestedName::getWriterService())
        );
    }
}
