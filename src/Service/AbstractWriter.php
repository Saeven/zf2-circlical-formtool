<?php

declare(strict_types=1);

namespace Circlical\LaminasTools\Service;

class AbstractWriter
{
    private array $resourceTemplates;

    protected function getResourceTemplate(string $type): string
    {
        return $this->resourceTemplates[static::class][$type];
    }

    public function __construct(array $resourceTemplates)
    {
        $this->resourceTemplates = $resourceTemplates;
    }
}
