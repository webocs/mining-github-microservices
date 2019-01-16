<?php

namespace App;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use App\ServiceProviders;

class ServicesProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container->register(new ServiceProviders\LoggerProvider);
        $container->register(new ServiceProviders\IndexStoreProvider);
        $container->register(new ServiceProviders\IndexerProvider);
        $container->register(new ServiceProviders\CrawlerServiceProvider);
        $container->register(new ServiceProviders\ConsoleApplicationProvider);
    }
}
