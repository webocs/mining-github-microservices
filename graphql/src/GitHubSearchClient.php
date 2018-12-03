<?php

namespace App;

use RuntimeException;

/**
 * Simple client for repository search.
 */
class GitHubSearchClient
{
    /**
     * @var GraphQLClient
     */
    private $client;

    /**
     * @var string  GraphQL query for paginated reposutory listing
     */
    private $listQuery = <<<'GRAPHQL_QUERY'
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
     * @var string  GraphQL query for initial repo count
     */
    private $countQuery = <<<'GRAPHQL_QUERY'
query($searchString:String!) {
  search (type:REPOSITORY, query:$searchString) { repositoryCount }
}
GRAPHQL_QUERY;

    /**
     * Initializes a new GitHub client.
     *
     * @param   GraphQLClient   $client
     * @return  void
     */
    public function __construct(GraphQLClient $client)
    {
        $this->client = $client;
    }

    /**
     * Returns repository count.
     *
     * @param   string      $searchString   Search string
     * @return  integer
     */
    public function count($searchString)
    {
        $result = $this->client->execute($this->countQuery, [ 'searchString' => $searchString ]);

        return $result['data']['search']['repositoryCount'];
    }

    /**
     * Returns the repository list.
     *
     * @param   string      $searchString   Search string
     * @return  array
     */
    public function list($searchString)
    {
        $result = [];
        $cursor = null;

        do {
            // Fetch one page
            $page = $this->client->execute($this->listQuery, [
                'searchString'  => $searchString,
                'cursor'        => $cursor,
            ]);

            // Update cursor
            $cursor = $page['data']['search']['pageInfo']['endCursor'];

            // Merge results
            $result = array_merge($result, $page['data']['search']['edges']);
        } while($page['data']['search']['pageInfo']['hasNextPage']);

        return $result;
    }
}
