<?php

return [
    'router' => [
        'routes' => [
            'auth' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/auth[/]',
                    'defaults' => [
                        'controller' => 'AuthController'
                    ]
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'login' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'login[/]',
                            'defaults' => [
                                'action' => 'login'
                            ]
                        ]
                    ],
                    'logout' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'logout[/]',
                            'defaults' => [
                                'action' => 'logout'
                            ]
                        ],
                        'may_terminate' => true,
                        'child_route' => [
                            'confirm' => [
                                'type' => 'segment',
                                'options' => [
                                    'route' => 'confirm[/]',
                                    'defaults' => [
                                        'action' => 'confirmLogout'
                                    ]
                                ]
                            ],
                        ]
                    ],
                    'forgot' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'forgot[/]',
                            'defaults' => [
                                'action' => 'forgot'
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ],
    'controllers' => [
        'invokables' => [
            'AuthController' => \Dvsa\Olcs\Auth\Controller\DefaultController::class,
        ]
    ],
    'service_manager' => [

    ],
    'zfc_rbac' => [
        'guards' => [
            'ZfcRbac\Guard\RoutePermissionsGuard' => [
                'auth/*' => ['*'],
            ]
        ]
    ],
];
