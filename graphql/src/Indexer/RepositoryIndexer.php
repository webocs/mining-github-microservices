<?php

namespace App\Indexer;

use Psr\Log\LoggerInterface;
use App\Index\IndexStore;

class RepositoryIndexer
{
    /** @var GitHubSearchService */
    private $search;

    /** @var IndexStore */
    private $store;

    /** @var LoggerInterface */
    private $logger;

    /**
     * Initializes the service, injecting required dependencies.
     *
     * @param   GitHubSearchService     $search
     * @param   IndexStore              $store
     * @param   LoggerInterface         $logger
     * @return  void
     */
    public function __construct(GitHubSearchService $search, IndexStore $store, LoggerInterface $logger)
    {
        $this->search = $search;
        $this->store = $store;
        $this->logger = $logger;
    }

    /**
     * Executes configured search on github, does some filtering, and sets the index.
     *
     * @return  void
     */
    public function run()
    {
        $repositories = $this->search->run();

        $filtered = [];
        foreach ($repositories as $repo) {
            if ($repo['tagsYear'] < 2) {
                $this->logger->info('Ignoring {url}: it has less than 2 tags per year', [ 'url' => $repo['url'] ]);
            } else {
                $filtered[] = $repo;
            }
        }

        $this->store->putRepositoriesMetadata($filtered);
    }
}
