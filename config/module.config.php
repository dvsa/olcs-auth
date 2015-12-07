<?php

return [
    'router' => [
        'routes' => [
            'auth' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/auth[/]'
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'login' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'login[/]',
                            'defaults' => [
                                'controller' => 'Auth\LoginController',
                                'action' => 'index'
                            ]
                        ]
                    ],
                    'forgot-username' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'forgot-username[/]',
                            'defaults' => [
                                'controller' => 'Auth\ForgotUsernameController',
                                'action' => 'index'
                            ]
                        ]
                    ],
                    'forgot-password' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'forgot-password[/]',
                            'defaults' => [
                                'controller' => 'Auth\ForgotPasswordController',
                                'action' => 'index'
                            ]
                        ]
                    ],
                    'logout' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'logout[/]',
                            'defaults' => [
                                'controller' => 'Auth\LogoutController',
                                'action' => 'index'
                            ]
                        ],
                    ],
//                    'forgot' => [
//                        'type' => 'segment',
//                        'options' => [
//                            'route' => 'forgot[/]',
//                            'defaults' => [
//                                'action' => 'forgot'
//                            ]
//                        ]
//                    ]
                ]
            ]
        ]
    ],
    'controllers' => [
        'invokables' => [
            'Auth\LoginController' => \Dvsa\Olcs\Auth\Controller\LoginController::class,
            'Auth\LogoutController' => \Dvsa\Olcs\Auth\Controller\LogoutController::class,
            'Auth\ForgotUsernameController' => \Dvsa\Olcs\Auth\Controller\ForgotUsernameController::class,
            'Auth\ForgotPasswordController' => \Dvsa\Olcs\Auth\Controller\ForgotPasswordController::class,
            //'AuthController' => \Dvsa\Olcs\Auth\Controller\DefaultController::class,
        ]
    ],
    'service_manager' => [
        'factories' => [
            'Auth\AuthenticationService' => \Dvsa\Olcs\Auth\Service\Auth\AuthenticationService::class,
            'Auth\LogoutService' => \Dvsa\Olcs\Auth\Service\Auth\LogoutService::class,
            'Auth\CookieService' => \Dvsa\Olcs\Auth\Service\Auth\CookieService::class,
        ]
    ],
    'view_manager' => [
        'template_map' => [
            'auth/login' => __DIR__ . '/../view/auth/login.phtml',
            'auth/layout' => __DIR__ . '/../view/auth/layout.phtml',
        ],
        'template_path_stack' => [
            __DIR__ . '/../view/'
        ]
    ],
    'zfc_rbac' => [
        'guards' => [
            'ZfcRbac\Guard\RoutePermissionsGuard' => [
                'auth/*' => ['*'],
            ]
        ]
    ],
    'openam' => [
        'url' => null, // @NOTE This must be implemented
        'realm' => null,
        'cookie' => [
            'name' => 'secureToken',
            'domain' => null, // @NOTE This must be implemented
        ],
        'client' => [
            'options' => [
                'adapter' => \Zend\Http\Client\Adapter\Curl::class,
                'timeout' => 60,
            ]
        ]
    ]
];
