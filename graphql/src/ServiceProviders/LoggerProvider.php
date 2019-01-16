<?php

namespace App\ServiceProviders;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Processor\PsrLogMessageProcessor;

class LoggerProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container['logger'] = function ($container) {

            // Create a log channel
            $logger = new Logger('app');

            // Add PSR-3 compliant processor
            $logger->pushProcessor(new PsrLogMessageProcessor);

            // Log to file, with highest verbosity
            $logger->pushHandler(new StreamHandler(
                $container['settings']['app.file.log'],
                Logger::DEBUG
            ));

            return $logger;
        };
    }
}
