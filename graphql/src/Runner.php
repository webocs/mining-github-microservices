<?php

namespace App;

use Pimple\Container as PimpleContainer;
use Pimple\Psr11\Container;

class Runner
{
    /**
     * @var array Application settings
     */
    private static $settings;

    /**
     * @var array Application container
     */
    private static $container;

    /**
     * Returns, or builds, the container
     *
     * @return \Psr\ContainerInterface
     */
    public static function getContainer()
    {
        if (self::$container === null) {
            $container = new PimpleContainer([
                'settings' => self::getSettings(),
            ]);
            $container->register(new ServicesProvider);

            // Wrap Pimple with a PSR11 compatible implementation.
            self::$container = new Container($container);
        }

        return self::$container;
    }

    /**
     * Returns, or builds from environment, the settings array
     *
     * @return array
     */
    public static function getSettings()
    {
        if (self::$settings === null) {
            self::$settings = SettingsFactory::fromEnvironment();
        }
        return self::$settings;
    }

    /**
     * Runs cli application.
     *
     * @return  void
     */
    public static function runCli()
    {
        self::getContainer()->get('cli.app')->run();
    }
}
