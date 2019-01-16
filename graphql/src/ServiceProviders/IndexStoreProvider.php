<?php

namespace App\ServiceProviders;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use App\Index\IndexStore;

class IndexStoreProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        // Setup database connection
        $container['db'] = function ($container) {
            return DriverManager::getConnection(
                [
                    'driver' => 'pdo_sqlite',
                    'path' => $container['settings']['app.file.store']
                ],
                new Configuration
            );
        };

        // Setup index store
        $container['index_store'] = function($container) {
            return new IndexStore($container['db']);
        };
    }
}
