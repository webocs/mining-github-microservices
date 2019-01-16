<?php

namespace App;

use RuntimeException;

class SettingsFactory
{
    /**
     * Loads settings from array.
     *
     * @return  array
     */
    public static function fromArray(array $settings)
    {
        // Check for an aditional file with settings, and load it
        if (isset($settings['app.file.config']) && !empty($settings['app.file.config'])) {
            $config = self::loadConfigFile($settings['app.file.config']);
            $settings = array_merge($settings, $config);
        }

        // TODO: Validate required settings

        return $settings;
    }

    /**
     * Loads settings from environment.
     *
     * @return  array
     */
    public static function fromEnvironment()
    {
        $basePath = realpath(__DIR__.'/..');

        // TODO: Fetch required settings from environment.
        $settings = [
            'app.file.config'       => "$basePath/config.ini",
            'app.file.store'        => "$basePath/runtime/store.sqlite",
            'app.file.log'          => "$basePath/runtime/application.log",
            'app.path.repositories' => "$basePath/runtime/repositories",
        ];

        return self::fromArray($settings);
    }

    /**
     * Loads a config file, for merging in settings.
     *
     * @throws  RuntimeException   When unable to read file
     * @return  array
     */
    private static function loadConfigFile($file)
    {
        $result = @parse_ini_file($file, false, INI_SCANNER_RAW);

        // Verify correct parsing
        if ($result === false) {
            throw new RuntimeException("Unable to parse config file $file");
        }

        return $result;
    }
}
