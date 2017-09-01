# GitHub microservices miner

Gihub mining replication package for the article "Microservices in the Wild: the Github Landscape" 

## Run instructions

- Clone the project and fill the `config.json` file with your authentication credentials
- Run `npm install`
- Run `node app.js`

## What does it do?

The program will take the file `firstFilterRepositories.json` (which was created using Google BigQuery, by running the query in the `googleBigQueryFirstFilter.sql` file) and output the `reposWithStars.json` file which will contain the ids and urls of all repositories that have more than a certain number of stars. 

### Repository JSON structure

Al repositores in JSON files are decribed by the following structure

``
  {
      "type" : "repository",
      "created_repo_id" : 95419861,
      "created_repo_name" : "paragonie/chronicle",
      "created_repo_url" : "/repos/paragonie/chronicle"
    }
``

## Config

Config.json has 4 fields
```
{
 "makeAuthenticatedRequests` specifies":true,
 "authClientId":"",
 "authClientSecret":"",
 "starGazersTreshold":10
}
```

+The first field `makeAuthenticatedRequests` specifies wether or not you would like to make Authenticated Requests to the Github API. Authenticated requests have less limitations in terms of request per unit of time.

+The Second and third fields `authClientId` and  `authClientSecret` specifie your github API credentials in case you selected TRUE for the first field

+ The last field `starGazersTreshold` specifies the minimum of stars

