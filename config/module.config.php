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
                    'expired-password' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'expired-password/:authId[/]',
                            'defaults' => [
                                'controller' => 'Auth\ExpiredPasswordController',
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
                            ],
                        ],
                    ],
                    'reset-password' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'reset-password[/]',
                            'defaults' => [
                                'controller' => 'Auth\ResetPasswordController',
                                'action' => 'index'
                            ],
                        ],
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
                    ]
                ]
            ]
        ]
    ],
    'controllers' => [
        'invokables' => [
            'Auth\LoginController' => \Dvsa\Olcs\Auth\Controller\LoginController::class,
            'Auth\LogoutController' => \Dvsa\Olcs\Auth\Controller\LogoutController::class,
            'Auth\ExpiredPasswordController' => \Dvsa\Olcs\Auth\Controller\ExpiredPasswordController::class,
            'Auth\ForgotPasswordController' => \Dvsa\Olcs\Auth\Controller\ForgotPasswordController::class,
            'Auth\ResetPasswordController' => \Dvsa\Olcs\Auth\Controller\ResetPasswordController::class,
        ]
    ],
    'service_manager' => [
        'invokables' => [
            'Auth\ResponseDecoderService' => \Dvsa\Olcs\Auth\Service\Auth\ResponseDecoderService::class,
        ],
        'factories' => [
            'Auth\AuthenticationService' => \Dvsa\Olcs\Auth\Service\Auth\AuthenticationService::class,
            'Auth\ExpiredPasswordService' => \Dvsa\Olcs\Auth\Service\Auth\ExpiredPasswordService::class,
            'Auth\ForgotPasswordService' => \Dvsa\Olcs\Auth\Service\Auth\ForgotPasswordService::class,
            'Auth\ResetPasswordService' => \Dvsa\Olcs\Auth\Service\Auth\ResetPasswordService::class,
            'Auth\LoginService' => \Dvsa\Olcs\Auth\Service\Auth\LoginService::class,
            'Auth\LogoutService' => \Dvsa\Olcs\Auth\Service\Auth\LogoutService::class,
            'Auth\CookieService' => \Dvsa\Olcs\Auth\Service\Auth\CookieService::class,
            'Auth\Client' => \Dvsa\Olcs\Auth\Service\Auth\Client\Client::class,
            'Auth\Client\UriBuilder' => \Dvsa\Olcs\Auth\Service\Auth\Client\UriBuilder::class,
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
