<?php

declare(strict_types=1);

use Circlical\LaminasTools\Service\ControllerWriter;
use Circlical\LaminasTools\Service\FormWriter;

return [
    'circlical' => [
        'powertools' => [

            //
            // The command that opens files when you're all set.  Set this to null if you don't want this behavior or your editor
            // can't make this happen/
            //
            'command' => [
                'edit' => 'phpstorm %s',
            ],

            //
            // Override these with your own project templates if you like!
            //
            'resource_templates' => [
                ControllerWriter::class => [
                    ControllerWriter::RESOURCE_CONTROLLER => __DIR__ . '/../../src/Resources/Controller.txt',
                    ControllerWriter::RESOURCE_FACTORY => __DIR__ . '/../../src/Resources/ControllerFactory.txt',
                ],
                FormWriter::class => [
                    FormWriter::RESOURCE_FORM => __DIR__ . '/../../src/Resources/Form.txt',
                    FormWriter::RESOURCE_FACTORY => __DIR__ . '/../../src/Resources/FormFactory.txt',
                    FormWriter::RESOURCE_FACTORY_DOCTRINE => __DIR__ . '/../../src/Resources/DoctrineFormFactory.txt',
                    FormWriter::RESOURCE_FILTER => __DIR__ . '/../../src/Resources/InputFilter.txt',
                    FormWriter::RESOURCE_FILTER_FACTORY => __DIR__ . '/../../src/Resources/InputFilterFactory.txt',
                ],
            ],
        ],
    ],
];