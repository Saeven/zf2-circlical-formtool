<?php

declare(strict_types=1);

namespace Circlical\LaminasTools\Factory\Service;

use Laminas\ServiceManager\Factory\AbstractFactoryInterface;
use Psr\Container\ContainerInterface;

use function preg_match;

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
