<?php

declare(strict_types=1);

namespace Circlical\LaminasTools;

use Circlical\LaminasTools\Command\CreateControllerCommand;
use Circlical\LaminasTools\Command\CreateFormCommand;
use Circlical\LaminasTools\Factory\AbstractCommandFactory;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
            'laminas-cli' => $this->getConsoleConfig(),
        ];
    }


    public function getDependencies(): array
    {
        return [
            'abstract_factories' => [
                AbstractCommandFactory::class,
            ],
        ];
    }


    public function getConsoleConfig(): array
    {
        return [
            'commands' => [
                'ct:form' => CreateFormCommand::class,
                'ct:controller' => CreateControllerCommand::class,
            ],
        ];
    }
}
