<?php

namespace Dvsa\OlcsTest\Auth;

use Mockery as m;

error_reporting(-1);
chdir(dirname(__DIR__));
date_default_timezone_set('Europe/London');

/**
 * Test bootstrap, for setting up autoloading
 */
class Bootstrap
{
    protected static $config = [];

    public static function init()
    {
        ini_set('memory_limit', '1G');

        $loader = static::initAutoloader();
        $loader->addPsr4('Dvsa\\OlcsTest\\Auth\\', __DIR__ . '/src');

        self::$config = [
            'modules' => [
                'Dvsa\Olcs\Auth'
            ],
            'module_listener_options' => [
                'module_paths' => [
                    __DIR__ . '/../'
                ]
            ]
        ];
    }

    /**
     * Changed this method to return a mock
     *
     * @return \Zend\ServiceManager\ServiceManager
     */
    public static function getServiceManager()
    {
        $sm = m::mock('\Zend\ServiceManager\ServiceManager')
            ->makePartial()
            ->setAllowOverride(true);

        return $sm;
    }

    protected static function initAutoloader()
    {
        return require('vendor/autoload.php');
    }
}

Bootstrap::init();
