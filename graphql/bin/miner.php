#!/usr/bin/env php
<?php

define('APP_BASE_PATH', realpath(__DIR__."/.."));

require APP_BASE_PATH."/vendor/autoload.php";

use App\GitHubSearchClient;
use App\GraphQLClient;
use App\Miner;

//
// Build configuration
//
$gitHubToken = getenv('GHMINER_OAUTH_TOKEN');
if ($gitHubToken === false || empty($gitHubToken)) {
    throw new RuntimeException(
        "Environment valiable GHMINER_OAUTH_TOKEN must be defined, and contain a value."
    );
}
$gitHubEndpoint = getenv('GHMINER_ENDPOINT');
if ($gitHubEndpoint === false || empty($gitHubEndpoint)) {
    $gitHubEndpoint = "https://api.github.com/graphql";
}
$indexFile = APP_BASE_PATH."/out/miner-result.json";

// Build services
$client = new GraphQLClient($gitHubEndpoint, $gitHubToken);
$search = new GitHubSearchClient($client);
$miner = new Miner($search);

// Execute miner
$result = $miner->run();

// Ensure output directory and store result
@mkdir(dirname($indexFile), 0777, true);
file_put_contents($indexFile, json_encode($result, JSON_PRETTY_PRINT));
