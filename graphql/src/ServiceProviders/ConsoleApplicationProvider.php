<?php

namespace App\ServiceProviders;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use App\Console\Application;
use App\Console\IndexCommand;
use App\Console\FetchCommand;
use App\Console\SolrIndexCommand;

class ConsoleApplicationProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container['cli.app'] = function ($container) {
            $application = new Application($container['logger']);

            // Configure application
            $application->setName('GitHub Miner');
            if (isset($container['settings']['app.version'])) {
                $application->setVersion($container['settings']['app.version']);
            }

            // Add commands
            $application->add(new IndexCommand($container['repository_indexer_service']));
            $application->add(new FetchCommand($container['repository_crawler_service']));
            $application->add(new SolrIndexCommand);

            return $application;
        };
    }
}
