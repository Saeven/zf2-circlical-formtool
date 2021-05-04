<?php

declare(strict_types=1);

namespace Circlical\LaminasTools;

class Module
{
    public function getConfig(): array
    {
        return (new ConfigProvider())();
    }
}