<?php

namespace App;

class Miner
{
    /**
     * @var GitHubSearchClient
     */
    private $searchClient;

    /**
     * Initializes miner, injecting required dependencies.
     *
     * @param   GitHubSearchClient  $searchClient
     * @return  void
     */
    public function __construct(GitHubSearchClient $searchClient)
    {
        $this->searchClient = $searchClient;
    }

    /**
     * Executes repository fetching.
     *
     * @return  array
     */
    public function run()
    {
        $indexedData = [];
        $searches = [
            "microservice in:description stars:>=10 pushed:>=2018-09-18",
            "NOT microservice in:description topic:microservice stars:>=10 pushed:>=2018-09-18",
            "NOT microservice in:description topic:microservices stars:>=10 pushed:>=2018-09-18",
        ];

        // Iterate and execute every search
        foreach ($searches as $search) {
            $data = $this->searchClient->list($search);

            // Merge fetched data
            foreach ($data as $singleObject) {
                $objectKey = $singleObject['node']['id'];

                $indexedData[$objectKey] = $singleObject;
            }
        }

        return array_values($indexedData);
    }
}
