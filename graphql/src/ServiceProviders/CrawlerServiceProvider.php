<?php

namespace App\ServiceProviders;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use GitWrapper\GitWrapper;
use App\Crawl\CrawlService;
use App\Console\SearchCommand;

class CrawlerServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container['git_wrapper_service'] = function ($container) {
            $git = new GitWrapper;
            $git->setTimeout($container['settings']['git.timeout']);

            return $git;
        };

        $container['repository_crawler_service'] = function ($container) {
            return new CrawlService(
                $container['index_store'],
                $container['git_wrapper_service'],
                $container['settings']['app.path.repositories'],
                $container['logger']
            );
        };
    }
}
