<?php

namespace Dvsa\OlcsTest\Auth;

use Laminas\Mvc\Service\ServiceManagerConfig;
use Laminas\ServiceManager\ServiceManager;
use Laminas\I18n\Translator\Translator;
use Laminas\Console\Console;

/**
 * Test bootstrap, for setting up autoloading
 */
class Bootstrap
{
    /** @var ServiceManager */
    protected static $serviceManager;

    /** @var array */
    protected static $config;

    public static function init()
    {
        Console::overrideIsConsole(false);

        $zf2ModulePaths = array(dirname(dirname(__DIR__)));
        if (($path = static::findParentPath('vendor'))) {
            $zf2ModulePaths[] = $path;
        }
        if (($path = static::findParentPath('module')) !== $zf2ModulePaths[0]) {
            $zf2ModulePaths[] = $path;
        }

        $config = [
            'modules' => [
                'Dvsa\Olcs\Auth',
            ],
            'module_listener_options' => [
                'module_paths' => [
                    __DIR__ . '/../',
                ],
            ],
        ];

        $serviceManager = new ServiceManager(new ServiceManagerConfig());
        $serviceManager->setService('ApplicationConfig', $config);
        $serviceManager->setService('translator', new Translator());
        $serviceManager->get('ModuleManager')->loadModules();

        // If we want to a mock a service, we can.  But default services apply.
        $serviceManager->setAllowOverride(true);

        self::$config = $config;
        self::$serviceManager = $serviceManager;
    }

    public static function getConfig()
    {
        return self::$config;
    }

    public static function getServiceManager()
    {
        return self::$serviceManager;
    }


    protected static function findParentPath($path)
    {
        $dir = __DIR__;
        $previousDir = '.';
        while (!is_dir($dir . DIRECTORY_SEPARATOR . $path)) {
            $dir = dirname($dir);
            if ($previousDir === $dir) {
                return false;
            }
            $previousDir = $dir;
        }
        return $dir . DIRECTORY_SEPARATOR . $path;
    }
}
