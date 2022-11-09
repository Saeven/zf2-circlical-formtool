<?php

declare(strict_types=1);

namespace Circlical\LaminasTools;

use Laminas\ModuleManager\Feature\ConfigProviderInterface;

use function array_merge;

class Module implements ConfigProviderInterface
{
    public function getConfig(): array
    {
        $configProvider = new ConfigProvider();
        $options = require __DIR__ . "/../config/autoload/console.global.php";

        return array_merge([
            'laminas-cli' => $configProvider->getConsoleConfig(),
            'service_manager' => $configProvider->getDependencies(),
        ], $options);
    }
}
