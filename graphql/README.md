# GitHub microservices miner - GraphQL version

Fetches repositories data, using [GitHub's GraphQL API].

[GitHub's GraphQL API]: https://developer.github.com/v4/

## What does it do?

The program works in two stages: *mining* and *processing*.

### Mining stage

Triggered by `bin/miner.php`, will try to fetch an index of repositories from GitHub,
with some useful aggregate data. The result can be checked on `out/miner-result.json`.

### Processing stage

Triggered by `bin/processor.php`, will iterate the raw index data, providing a more
"flat" dataset. It will add some computed fields to.

Files resulting from processor are:

 - `out/processor-result.json`: same array as index, but normalized.
 - `out/repositories.csv`: plain CSV with the collected data for repositories.
 - `out/statistics.csv`: plain CSV with global aggerate data (number of repos, etc).
 - `out/topics.csv`: list of topics, with ocurrences count.
 - `out/languages.csv`: list of lenguages, with ocurrences count.

## Requirements

This version requires:

 - [PHP 7+](http://php.net)
 - [Composer](https://getcomposer.org/)
 - A [GitHub OAuth token](https://help.github.com/articles/creating-an-access-token-for-command-line-use/)

## Run instructions

Get ready:

 - Go to graphql directory.
 - Run: `composer install`

Execute miner:
 - Run `GHMINER_OAUTH_TOKEN=your_private_token bin/miner.php`

Execute processor:
 - Run `bin/processor.php`
