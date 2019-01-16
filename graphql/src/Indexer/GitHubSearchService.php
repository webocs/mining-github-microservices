<?php

namespace App\Indexer;

use DateTimeImmutable;
use Psr\Log\LoggerInterface;

/**
 * Fetches an array with repository metadata, using GitHub's GraphQL api.
 */
class GitHubSearchService
{
    /** @var GraphQLClient GraphQL client instance */
    private $client;

    /** @var array GitHub search strings */
    private $searches;

    /** @var string GraphQL query string */
    private $query = <<<'GRAPHQL_QUERY'
query($searchString:String!, $cursor:String, $step:Int = 50) {
  search (type:REPOSITORY, query:$searchString, first:$step, after:$cursor) {
    repositoryCount
    edges {
      node {
        ... on Repository {
          id
          databaseId
          name
          url
          description
          repositoryTopics (first: 50) { nodes { topic { name } } }
          primaryLanguage { name }
          stargazers { totalCount }
          watchers { totalCount }
          refs (refPrefix:"refs/tags/") { totalCount }
          releases { totalCount }
          pullRequests { totalCount }
          issues { totalCount }
          createdAt
          pushedAt
          updatedAt
        }
      }
    }
    pageInfo { endCursor hasNextPage }
  }
}
GRAPHQL_QUERY;

    /**
     * Initializes a new repository seearch service.
     *
     * @param   GraphQLClient   $client
     * @param   array           $searches   GitHub search strings
     * @param   LoggerInterface $logger     Logger instance
     * @return  void
     */
    public function __construct(GraphQLClient $client, array $searches, LoggerInterface $logger)
    {
        $this->client = $client;
        $this->searches = $searches;
        $this->logger = $logger;
    }

    /**
     * Executes configured searches, and merges them into a single array.
     *
     * @return  array
     */
    public function run()
    {
        $result = [];
        $iteration = 0;

        $this->logger->debug("Executing {searches} searches.", [ 'searches' => count($this->searches) ]);

        // Iterate and execute every search
        foreach ($this->searches as $search) {

            $iteration++;
            $this->logger->debug(
                "Executing search {iteration}/{searches}.",
                [
                    'iteration' => $iteration,
                    'searches' => count($this->searches),
                    'searchString' => $search,
                ]
            );

            $data = $this->runSingle($search);

            $this->logger->debug(
                "Got {count} results.",
                [ 'count' => count($data) ]
            );

            // Merge fetched data
            foreach ($data as $singleObject) {
                $objectKey = $singleObject['node']['id'];

                $result[$objectKey] = $this->flatten($singleObject);
            }
        }

        $this->logger->debug("Got {count} results after merge.", [ 'count' => count($result) ]);

        return array_values($result);
    }

    /**
     * Executes single search.
     *
     * @param   string  $search     Search string to execute
     * @return  array
     */
    private function runSingle($search)
    {
        $result = [];
        $cursor = null;
        $maxTries = 3;
        $sleep = 10;

        do {
            $tries = 1;
            $success = false;

            // Fetch one page, allowing fails
            while (!$success && $tries <= $maxTries) {
                try {
                    $page = $this->client->execute($this->query, [
                        'searchString'  => $search,
                        'cursor'        => $cursor,
                    ]);
                    $success = true;
                } catch (\Exception $e) {

                    // Inform about error
                    $this->logger->warning(
                        "Query page fetch failed, at attempt {tries}/{maxTries}",
                        [ 'tries' => $tries, 'maxTries' => $maxTries ]
                    );

                    // Check tries, and re-throw last exception when giving up
                    if ($tries < $maxTries) {
                        $this->logger->warning("Waiting {sleep} seconds to retry.", [ 'sleep' => $sleep ]);
                        sleep($sleep);
                    } else {
                        throw $e;
                    }

                    // Update tries
                    $tries++;
                }
            }

            // Update cursor
            $cursor = $page['data']['search']['pageInfo']['endCursor'];

            // Merge results
            $result = array_merge($result, $page['data']['search']['edges']);
        } while($page['data']['search']['pageInfo']['hasNextPage']);

        return $result;
    }

    /**
     * Flatten repository data, and adds some computed data.
     *
     * @param   array   $raw        Raw repo data, as retrieved from GitHub API
     * @return  array
     */
    private function flatten($raw)
    {
        // Flatten repo data
        // -----------------------------------------------------------------
        $result = [
            'id'            => $raw['node']['id'],
            'databaseId'    => (int) $raw['node']['databaseId'],
            'name'          => $raw['node']['name'],
            'url'           => $raw['node']['url'],
            'description'   => $raw['node']['description'],
            'topics'        => array_map(function ($topicNode) {
                return $topicNode['topic']['name'];
            }, $raw['node']['repositoryTopics']['nodes']),
            'language'      => $raw['node']['primaryLanguage']['name'],
            'stars'         => (int) $raw['node']['stargazers']['totalCount'],
            'watchers'      => (int) $raw['node']['watchers']['totalCount'],
            'tags'          => (int) $raw['node']['refs']['totalCount'],
            'releases'      => (int) $raw['node']['releases']['totalCount'],
            'pullRequests'  => (int) $raw['node']['pullRequests']['totalCount'],
            'issues'        => (int) $raw['node']['issues']['totalCount'],
            'created'       => $raw['node']['createdAt'],
            'pushed'        => $raw['node']['pushedAt'],
            'updated'       => $raw['node']['updatedAt'],
        ];

        // Enrich data
        // -----------------------------------------------------------------
        $result['releasesYear'] = $this->yearAverage($result['created'], $result['pushed'], $result['releases']);
        $result['tagsYear'] = $this->yearAverage($result['created'], $result['pushed'], $result['tags']);

        return $result;
    }

    /**
     * Computes year average of value, given a range of dates.
     *
     * For example, after:
     *
     *     $avg = $this->yearAverage(2017-01-01T15:15:15Z, 2018-01-01T15:15:15Z, 15)
     *
     * `$avg` should be `15`.
     */
    private function yearAverage($from, $to, $count)
    {
        $from = new DateTimeImmutable($from);
        $to = new DateTimeImmutable($to);

        /** @var DateInterval $diff */
        $diff = $from->diff($to);

        // Get years... rounded!
        $years = $diff->y + 1;

        return $count/$years;
    }
}
