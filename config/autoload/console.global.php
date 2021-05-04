<?php

declare(strict_types=1);

use Circlical\LaminasTools\Command\CreateFormCommand;

return [
    'laminas-cli' => [
        'commands' => [
            'ct:create-form' => CreateFormCommand::class,
        ],
    ],
];