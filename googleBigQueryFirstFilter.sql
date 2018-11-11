-- This is the first applied filter. It was used to create the "firstFilterRepositories.json" file
-- Selects repositories ID, name and URL for
-- * All GitHub Repositories that, in a time peridod between 2018-01-01 and 2018-10-31:
--   * Have been tagged at least once
--   * Have the text "microservice" in their description
--   * Have at least 50 push events in the same time window
--
-- WARNING: This query was created for Google Big query and GithubArchive database, it takes 900GB+ of processing.

SELECT
    created.repo.id,
    created.repo_name,
    created.repo_url
FROM (
    SELECT repo.id, LAST(repo.name) as repo_name, LAST(repo.url) as repo_url
    FROM (
        TABLE_DATE_RANGE(
            [githubarchive:day.],
            TIMESTAMP('2018-01-01'),
            TIMESTAMP('2018-10-31')
        )
    )
    WHERE type = 'CreateEvent'
        AND JSON_EXTRACT_SCALAR(payload, '$.ref_type') = 'tag'
        AND JSON_EXTRACT_SCALAR(payload, '$.description') LIKE '%microservice%'
    GROUP BY repo.id
) AS created
JOIN (
    SELECT repo.id, count(*) as pushevents
    FROM (
        TABLE_DATE_RANGE(
            [githubarchive:day.],
            TIMESTAMP('2018-01-01'),
            TIMESTAMP('2018-10-31')
        )
    )
    WHERE type = 'PushEvent'
    GROUP BY repo.name, repo.id
    HAVING count(*) > 50
    ORDER BY pushevents DESC
) AS pushed
    ON created.repo.id = pushed.repo.id
