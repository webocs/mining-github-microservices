#!/usr/bin/env php
<?php

define('APP_BASE_PATH', realpath(__DIR__."/.."));

require APP_BASE_PATH."/vendor/autoload.php";

use App\Processor;

//
// Build configuration
//
$indexFile = APP_BASE_PATH."/out/miner-result.json";
$resultFile = APP_BASE_PATH."/out/processor-result.json";
$reposCsv = APP_BASE_PATH."/out/repositories.csv";
$topicsCsv = APP_BASE_PATH."/out/topics.csv";
$statisticsCsv = APP_BASE_PATH."/out/statistics.csv";
$languagesCsv = APP_BASE_PATH."/out/languages.csv";

// Load repo index
if (!is_file($indexFile) || !is_readable($indexFile)) {
    throw new RuntimeException("$indexFile does not exits. Run miner.php first.");
}
$index = json_decode(file_get_contents($indexFile), true);

// Build service
$processor = new Processor;

// Execute processor
$result = $processor->run($index);

// Store result
file_put_contents($resultFile, json_encode($result, JSON_PRETTY_PRINT));

// Store processed data as CSV files
// - Statistics
$f = fopen($statisticsCsv, 'w');
foreach($result['meta']['statistics'] as $name => $value) {
    fputcsv($f, [$name, $value]);
}
fclose($f);
// - Languages
$languages = $result['meta']['languages'];
if (count($languages) > 0) {
    $f = fopen($languagesCsv, 'w');
    fputcsv($f, array_keys($languages[0]));
    foreach($languages as $language) {
        fputcsv($f, array_values($language));
    }
    fclose($f);
}
// - Topics
$topics = $result['meta']['topics'];
if (count($topics) > 0) {
    $f = fopen($topicsCsv, 'w');
    fputcsv($f, array_keys($topics[0]));
    foreach($topics as $topic) {
        fputcsv($f, array_values($topic));
    }
    fclose($f);
}
// - Repositories
if (count($result['repositories']) > 0) {
    $f = fopen($reposCsv, 'w');
    fputcsv($f, array_keys($result['repositories'][0]));
    foreach($result['repositories'] as $repository) {
        // Flatten topic list
        $repository['topics'] = implode(',', $repository['topics']);
        fputcsv($f, array_values($repository));
    }
    fclose($f);
}
