<?php


    namespace CirclicalFormTool;

    use CirclicalFormTool\Controller\CliController;

    return [

        'controllers' => [
            'invokables' => [
	            CliController::class => CliController::class,
            ],
        ],

        'console' => [
	        'router' => [
	            'routes' => [
	                'create-form' => [
	                    'options' => [
	                        'route' => 'formtool create [--doctrine|-d]:doctrine <module> <form> [<class>]',
	                        'defaults' => [
	                            'controller' => CliController::class,
	                            'action' => 'create-form',
	                        ],
	                    ],
	                ],
	            ],
	        ],
	    ],

        'bjyauthorize' => [
	        'guards' => [
	            'BjyAuthorize\Guard\Controller' => [
	                ['controller' => CliController::class, 'roles' => []],
	            ],
	        ],
        ],
	];