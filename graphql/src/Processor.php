<?php

namespace App;

use DateTimeImmutable;

class Processor
{
    public function run(array $data)
    {
        // Initialize aggregate data.
        // ---------------------------------------------------------------------

        /** @var int */
        $maxStars = 0;
        /** @var array Array of topic => ocurrences */
        $topics = [];
        /** @var array Array of language => ocurrences */
        $languages = [];
        /** @var array Array of flat repository data */
        $repositories = [];

        // Iterate raw data
        // ---------------------------------------------------------------------

        //  - First: flatten each repo data
        //  - Second: enrich data (e.g. compute tags per year)
        //  - Third: aggregate data (e.g. compute max stars)
        foreach ($data as $node) {

            // Flatten repo data
            // -----------------------------------------------------------------
            $row = [
                'id'            => $node['node']['id'],
                'databaseId'    => (int) $node['node']['databaseId'],
                'name'          => $node['node']['name'],
                'url'           => $node['node']['url'],
                'description'   => $node['node']['description'],
                'topics'        => array_map(function ($topicNode) {
                    return $topicNode['topic']['name'];
                }, $node['node']['repositoryTopics']['nodes']),
                'language'      => $node['node']['primaryLanguage']['name'],
                'stars'         => (int) $node['node']['stargazers']['totalCount'],
                'watchers'      => (int) $node['node']['watchers']['totalCount'],
                'tags'          => (int) $node['node']['refs']['totalCount'],
                'releases'      => (int) $node['node']['releases']['totalCount'],
                'pullRequests'  => (int) $node['node']['pullRequests']['totalCount'],
                'issues'        => (int) $node['node']['issues']['totalCount'],
                'created'       => $node['node']['createdAt'],
                'pushed'        => $node['node']['pushedAt'],
                'updated'       => $node['node']['updatedAt'],
            ];

            // Enrich data
            // -----------------------------------------------------------------
            $row['releasesYear'] = $this->yearAverage($row['created'], $row['pushed'], $row['releases']);
            $row['tagsYear'] = $this->yearAverage($row['created'], $row['pushed'], $row['tags']);

            // Aggregate data
            // -----------------------------------------------------------------
            // Process max stars
            $maxStars = max($maxStars, $row['stars']);
            // Process topics
            foreach ($row['topics'] as $topic) {
                $topics[$topic] = isset($topics[$topic]) ? $topics[$topic] + 1 : 1;
            }
            // Process languages
            $language = $row['language'];
            if (! empty($language)) {
                $languages[$language] = isset($languages[$language]) ? $languages[$language] + 1 : 1;
            }
            // Aggregate data
            $repositories[] = $row;
        }

        // Complete/enrich aggregate data
        // ---------------------------------------------------------------------

        $repoCount = count($repositories);
        $languagesCount = count($languages);
        $topicsCount = count($topics);

        // Enrich topics aggregation
        arsort($topics);
        $topicOcurrences = [];
        foreach ($topics as $name => $ocurrences) {
            $topicOcurrences[] = [
                'name'          =>  $name,
                'ocurrences'    =>  $ocurrences,
                'share'         =>  $ocurrences/$repoCount,
            ];
        }

        // Enrich language ocurrences
        arsort($languages);
        $languageOcurrences = [];
        foreach ($languages as $name => $ocurrences) {
            $languageOcurrences[] = [
                'name'          => $name,
                'ocurrences'    => $ocurrences,
                'share'         => $ocurrences/$repoCount,
            ];
        }

        // Ready
        return [
            'repositories'  => $repositories,
            'meta'          => [
                'statistics'    => [
                    'count'         => $repoCount,
                    'maxStars'      => $maxStars,
                    'topicsCount'   => $topicsCount,
                    'languagesCount'    => $languagesCount,
                ],
                'languages'     => $languageOcurrences,
                'topics'        => $topicOcurrences,
            ],
        ];
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
