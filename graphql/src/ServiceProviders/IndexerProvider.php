<?php

namespace App\ServiceProviders;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use App\Indexer\GraphQLClient;
use App\Indexer\GitHubSearchService;
use App\Indexer\RepositoryIndexer;

class IndexerProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        // Build GraphQL client
        $container['graphql_client'] = function ($container) {
            return new GraphQLClient(
                $container['settings']['api.endpoint'],
                $container['settings']['api.token']
            );
        };

        // Build GitHub search service
        $container['github_search_service'] = function ($container) {
            // Build searches
            $searches = [];
            foreach ($container['settings']['api.base_searches'] as $baseSearch) {
                $searches[] = sprintf(
                    '%s stars:>=%s pushed:>=%s',
                    $baseSearch,
                    $container['settings']['api.search_min_stars'],
                    $container['settings']['api.search_min_pushed_at']
                );
            }

            return new GitHubSearchService($container['graphql_client'], $searches, $container['logger']);
        };

        // Build indexer service.
        $container['repository_indexer_service'] = function ($container) {
            return new RepositoryIndexer(
                $container['github_search_service'],
                $container['index_store'],
                $container['logger']
            );
        };
    }
}
