<?php

declare(strict_types=1);

use Laminas\ConfigAggregator\ConfigAggregator;

$aggregator = new ConfigAggregator([
    \Circlical\LaminasTools\ConfigProvider::class,
]);

return $aggregator->getMergedConfig();