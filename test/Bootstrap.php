<?php

// phpcs:disable
namespace Circlical\LaminasToolsTest;

use Laminas\ServiceManager\ServiceManager;

class Bootstrap
{
    protected static ?ServiceManager $serviceManager;

    protected static ?array $config;

    /**
     * Initialize bootstrap
     */
    public static function init()
    {
        // Load the user-defined test configuration file
        if (!is_file($sApplicationConfigPath = __DIR__ . '/config/application.config.php')) {
            throw new \LogicException(sprintf(
                'Application configuration file "%s" does not exist',
                $sApplicationConfigPath
            ));
        }
        if (false === ($aApplicationConfig = include $sApplicationConfigPath)) {
            throw new \LogicException(sprintf(
                'An error occured while including application configuration file "%s"',
                $sApplicationConfigPath
            ));
        }

        // Prepare the service manager
        static::$config = $aApplicationConfig;
//        $serviceManager = new \Laminas\ServiceManager\ServiceManager();
//        $oServiceManagerConfig = new \Laminas\Mvc\Service\ServiceManagerConfig(static::$config['service_manager'] ?? []);
//        $oServiceManagerConfig->configureServiceManager($serviceManager);
//        $serviceManager->setService('ApplicationConfig', static::$config);
//
//        // Load modules
//        $serviceManager->get('ModuleManager')->loadModules();
//        $application = $serviceManager->get('Application');
//        $application->bootstrap();

//        static::$serviceManager = $serviceManager;
    }

    public static function getServiceManager(): ServiceManager
    {
        return static::$serviceManager;
    }

    public static function getConfig(): array
    {
        return static::$config;
    }

    /**
     * Retrieve parent for a given path
     *
     * @param string $sPath
     *
     * @return boolean|string
     */
    protected static function findParentPath(string $sPath)
    {
        $sCurrentDir = __DIR__;
        $sPreviousDir = '.';
        while (!is_dir($sPreviousDir . '/' . $sPath)) {
            $sCurrentDir = dirname($sCurrentDir);
            if ($sPreviousDir === $sCurrentDir) {
                return false;
            }
            $sPreviousDir = $sCurrentDir;
        }

        return $sCurrentDir . '/' . $sPath;
    }
}

error_reporting(E_ALL | E_STRICT);

// Composer autoloading
if (!file_exists($sComposerAutoloadPath = __DIR__ . '/../vendor/autoload.php')) {
    throw new \LogicException('Composer autoload file "' . $sComposerAutoloadPath . '" does not exist');
}
if (false === (include $sComposerAutoloadPath)) {
    throw new \LogicException(sprintf(
        'An error occurred while including composer autoload file "%s"',
        $sComposerAutoloadPath
    ));
}

\Circlical\LaminasToolsTest\Bootstrap::init();