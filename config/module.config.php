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
                    ],
                    'validate' => [
                        'type' => \Zend\Mvc\Router\Http\Segment::class,
                        'options' => [
                            'route' => 'validate[/]',
                            'defaults' => [
                                'controller' => \Dvsa\Olcs\Auth\Controller\ValidateController::class,
                                'action' => 'index',
                            ]
                        ],
                    ],
                ]
            ],
            'change-password' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/change-password[/]',
                    'defaults' => [
                        'controller' => 'Auth\ChangePasswordController',
                        'action' => 'index'
                    ],
                ],
            ],
        ]
    ],
    'controllers' => [
        'invokables' => [
            'Auth\LoginController' => \Dvsa\Olcs\Auth\Controller\LoginController::class,
            'Auth\ExpiredPasswordController' => \Dvsa\Olcs\Auth\Controller\ExpiredPasswordController::class,
            'Auth\ForgotPasswordController' => \Dvsa\Olcs\Auth\Controller\ForgotPasswordController::class,
            'Auth\ChangePasswordController' => \Dvsa\Olcs\Auth\Controller\ChangePasswordController::class,
            'Auth\ResetPasswordController' => \Dvsa\Olcs\Auth\Controller\ResetPasswordController::class,
        ],
        'aliases' => [
            'Auth\LogoutController' => \Dvsa\Olcs\Auth\Controller\LogoutController::class,
        ],
        'factories' => [
            \Dvsa\Olcs\Auth\Controller\ValidateController::class =>
                \Dvsa\Olcs\Auth\Controller\ValidateController::class,
            \Dvsa\Olcs\Auth\Controller\LogoutController::class =>
                \Dvsa\Olcs\Auth\ControllerFactory\LogoutControllerFactory::class,
        ]
    ],
    'service_manager' => [
        'invokables' => [
            'Auth\ResponseDecoderService' => \Dvsa\Olcs\Auth\Service\Auth\ResponseDecoderService::class,
        ],
        'factories' => [
            'Auth\AuthenticationService' => \Dvsa\Olcs\Auth\Service\Auth\AuthenticationService::class,
            'Auth\ExpiredPasswordService' => \Dvsa\Olcs\Auth\Service\Auth\ExpiredPasswordService::class,
            \Dvsa\Olcs\Auth\Service\Auth\ForgotPasswordService::class =>
                \Dvsa\Olcs\Auth\Service\Auth\ForgotPasswordService::class,
            'Auth\ResetPasswordService' => \Dvsa\Olcs\Auth\Service\Auth\ResetPasswordService::class,
            'Auth\ChangePasswordService' => \Dvsa\Olcs\Auth\Service\Auth\ChangePasswordService::class,
            'Auth\LoginService' => \Dvsa\Olcs\Auth\Service\Auth\LoginService::class,
            'Auth\LogoutService' => \Dvsa\Olcs\Auth\Service\Auth\LogoutService::class,
            'Auth\CookieService' => \Dvsa\Olcs\Auth\Service\Auth\CookieService::class,
            \Dvsa\Olcs\Auth\Service\Auth\ValidateService::class =>
                \Dvsa\Olcs\Auth\Service\Auth\ValidateService::class,
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
        ],
        'strategies' => [
            'ViewJsonStrategy',
        ],
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
    ],
    'selfserve_logout_redirect_url' => 'http://gov.uk/done/vehicle-operator-licensing',
];
