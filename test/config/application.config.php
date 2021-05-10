<?php

use Laminas\Router;

return [
    'modules' => [
        'Laminas\Form',
        'Laminas\InputFilter',
        'Laminas\Router',
    ],
    'module_listener_options' => [
        'config_glob_paths' => [__DIR__ . '/module-config.php'],
        'module_paths' => ['vendor'],
    ],
    'factories' => [
        'Router' => Router\RouterFactory::class,
    ],

    'form_elements' => [
        'factories' => [
            \Circlical\TailwindFormsTest\Form\ChildForm::class => \Laminas\ServiceManager\Factory\InvokableFactory::class,
        ],
    ],
];