var rep=require('./firstFilterRepositories.json');
var https =require('https');
var fs = require('fs');
var forEach = require("async-foreach").forEach;
var config= require("./config.json");

var reposWithStars=[];

// Open JSON array
fs.appendFile("./reposWithStars.json", "[" , function(err) {
    if(err) {
        return console.log(err);
    }
});

//Iterating firstFilterRepositories.json file
//Large response, manually throttling http requests with async foreach
forEach( rep.repos, function(item, index, arr) {
    getData(item);
    var done = this.async();
    setTimeout(function() {
        done();
    }, 2000);
}, allDone);

//Close JSON array
function allDone(notAborted, arr) {
    console.log("done", notAborted, arr);
    fs.appendFile("./reposWithStars.json", "]" , function(err) {
        if(err) {
            return console.log(err);
        }
    });
}


//Getting stargazers array
function getData(item){
    var path="";

    //Auth requests have less restrictions in the github API
    if(config.makeAuthenticatedRequests)
        path=item.created_repo_url+"/stargazers?client_id="+config.authClientId+"&client_secret="+config.authClientSecret;
    else
        path=item.created_repo_url+"/stargazers";

    //Request options, configure your useragent name in the headers field
    var optStarGazersRequest = {
        host: "api.github.com",
        port: 443,
        path: path,
        method: 'GET',
        headers:{"User-Agent":"Your User Agent name"}
    };

    https.request(optStarGazersRequest, function(res) {
        data="";
        res.setEncoding('utf8');
        res.on('data', function (chunk) {
            data+=chunk;
        });

        res.on('end', function (d) {
            if(res.statusCode==200)
            {

                if(JSON.parse(data).length>config.starGazersTreshold)
                    reposWithStars.push(item);
                item.fullUrl="https://github.com"+(item.created_repo_url).replace("/repos","");
                console.log(item);

                fs.appendFile("./reposWithStars.json", JSON.stringify(item)+",\n" , function(err) {
                    if(err) {
                        return console.log(err);
                    }
                });
            }
        });

    }).end();
}