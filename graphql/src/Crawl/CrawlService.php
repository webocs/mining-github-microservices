<?php

namespace App\Crawl;

use DateTimeImmutable;
use Psr\Log\LoggerInterface;
use App\Index\IndexStore;
use GitWrapper\GitWrapper;

/**
 * Fetches an array with repository metadata, using GitHub's GraphQL api.
 */
class CrawlService
{
    /** @var DataStore */
    private $store;

    /** @var LoggerInterface */
    private $logger;

    /**
     * Initializes repository crawler.
     *
     * @param   DataStore           $store
     * @param   string              $path
     * @param   LoggerInterface     $logger
     * @return  void
     */
    public function __construct(IndexStore $store, GitWrapper $git, $path, LoggerInterface $logger)
    {
        $this->store = $store;
        $this->path = $path;
        $this->logger = $logger;
        $this->git = $git;
    }

    public function run()
    {
        $repositories = $this->store->getRepositories();

        $this->logger->debug("Got {count} repositories on index.", [ 'count' => count($repositories) ]);

        foreach ($repositories as $repository) {
            $this->fetch($repository);
        }
    }

    public function fetch($repository)
    {
        $id = $repository['id'];
        $url = $repository['url'];

        // Compute relative path, e.g: "https://github.com/user/repo" becomes "user/repo"
        $relativePath = ltrim(parse_url($url, PHP_URL_PATH), '/');

        // Compute absolute path.
        $path = $this->path . '/' . $relativePath;

        // Create directory, if needed (first clone).
        if (! is_dir($path)) {
            @mkdir($path, 0777, true);
        }

        // Check if path is a git repo (clone vs pull)
        if (! is_dir($path.'/.git')) {
            $this->logger->debug("Cloning {url} into {path} ...", [ 'url' => $url, 'path' => $path ]);
            $this->git->cloneRepository($url, $path);
        } else {
            $this->logger->debug("Fetching {url} into {path} ...", [ 'url' => $url, 'path' => $path ]);
            $this->git->git('pull --prune', $path);
        }

        $this->store->markGitFetched($id, $relativePath, new DateTimeImmutable('now'));
    }
}
