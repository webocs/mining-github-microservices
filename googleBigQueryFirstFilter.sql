-- This is the first applied filter. It was used to create the "firstFilterRepositories.json" file
-- Selects repositories ID, name and URL for
-- * All Github Repositories that were created between a 7 months window  2017/01/01 and 2017/08/29 which:
--   * Have at least one tag create in that time window containing the word "microservice"
--   * Have at least 50 push events in that time window
--
-- WARNING: This query was created for Google Big query and GithubArchive database, it takes 500GB+ of processing
-- and returns duplicated repos that you should remove.

SELECT
  created.repo.id,
  created.repo.name,
  created.repo.url
FROM(
      SELECT repo.id , payload,repo.name,repo.url
              FROM
              (
                  TABLE_DATE_RANGE(
                    [githubarchive:day.],
                    TIMESTAMP('2017-01-01'),
                    TIMESTAMP('2017-08-29')
                   )
               )
      WHERE
        type='CreateEvent' AND
        JSON_EXTRACT(payload, '$.ref_type') ='"tag"'
      GROUP BY repo.id,payload,repo.name,repo.url
) as created
JOIN
(
  SELECT repo.id, count(*) as pushevents
  FROM (
      TABLE_DATE_RANGE([githubarchive:day.],
        TIMESTAMP('2017-01-01'),
        TIMESTAMP('2017-08-29')
      )
  )
  where type='PushEvent'
  group by repo.name,repo.id
  having count(*)>50
  order by pushevents DESC
)      as pushed
ON
  created.repo.id==pushed.repo.id
WHERE  JSON_EXTRACT(created.payload, '$.description') LIKE "%microservice%"






