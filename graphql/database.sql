CREATE TABLE `repositories` (
    -- Basic repository data
    `id`                text,
    `name`              text NOT NULL,
    `url`               text NOT NULL,
    `description`       text,

    -- GitHub fetched metadata
    `gh_metadata`       json,
    `gh_metadata_fetched_at`    datetime,

    -- Local repository clone
    `git_path`          text,
    `git_fetched_at`    datetime,

    PRIMARY KEY(`id`)
);
