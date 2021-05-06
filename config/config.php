<?php

declare(strict_types=1);

use Laminas\ConfigAggregator\ConfigAggregator;

$aggregator = new ConfigAggregator([
    \Circlical\LaminasTools\ConfigProvider::class,

    // Load application config in a pre-defined order in such a way that local settings
    // overwrite global settings. (Loaded as first to last):
    //   - `global.php`
    //   - `*.global.php`
    //   - `local.php`
    //   - `*.local.php`
    new \Laminas\ConfigAggregator\PhpFileProvider('config/autoload/{{,*.}global,{,*.}local}.php'),
]);

return $aggregator->getMergedConfig();