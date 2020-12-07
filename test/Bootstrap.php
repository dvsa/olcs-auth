<?php

namespace Dvsa\OlcsTest\Auth;

use Laminas\Mvc\Service\ServiceManagerConfig;
use Laminas\ServiceManager\ServiceManager;
use Laminas\I18n\Translator\Translator;
use Laminas\Loader\AutoloaderFactory;
use Laminas\Console\Console;
use RuntimeException;

date_default_timezone_set('Europe/London');
error_reporting(E_ALL & ~E_USER_DEPRECATED);
chdir(__DIR__);

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

        self::initAutoloader();

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

    public static function chroot()
    {
        $rootPath = dirname(static::findParentPath(''));
        chdir($rootPath);
    }

    public static function getServiceManager()
    {
        return self::$serviceManager;
    }

    protected static function initAutoloader()
    {
        $vendorPath = static::findParentPath('vendor');

        if (file_exists($vendorPath . '/autoload.php')) {
            include $vendorPath . '/autoload.php';
        }

        if (! class_exists('Laminas\Loader\AutoloaderFactory')) {
            throw new RuntimeException(
                'Unable to load ZF2. Run `php composer.phar install`'
            );
        }

        AutoloaderFactory::factory(
            [
                'Laminas\Loader\StandardAutoloader' => [
                    'autoregister_zf' => true,
                    'namespaces' => [
                        __NAMESPACE__ => __DIR__ . DIRECTORY_SEPARATOR . __NAMESPACE__,
                    ],
                ],
            ]
        );
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

Bootstrap::init();
Bootstrap::chroot();
