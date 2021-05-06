<?php

declare(strict_types=1);

namespace Circlical\LaminasTools;

class Module
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
