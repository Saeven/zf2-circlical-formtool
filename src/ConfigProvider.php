<?php

declare(strict_types=1);

namespace Circlical\LaminasTools;

use Circlical\LaminasTools\Command\CreateFormCommand;

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
        return [];
    }

    public function getConsoleConfig(): array
    {
        return [
            'commands' => [
                // formtool create [--doctrine|-d]:doctrine <module> <form> [<class>]
                'ct:create-form' => CreateFormCommand::class,
            ],
        ];
    }

}


