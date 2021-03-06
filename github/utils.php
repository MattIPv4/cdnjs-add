<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

function destroyGHSession()
{
    foreach (array_keys($_SESSION) as $key) if (substr($key, 0, 3) === "GH_") unset($_SESSION[$key]);
}

function getGHClient()
{
    // https://packagist.org/packages/knplabs/github-api
    // https://github.com/KnpLabs/php-github-api/tree/master/doc
    $client = new \Github\Client();
    $client->authenticate($_SESSION['GH_oauth2token'], "", Github\Client::AUTH_URL_TOKEN);
    return $client;
}

function getAllGHFiles($client, $repoOwner, $repoName, $ref = "master", $path = "")
{
    // Get all files/dirs at this path
    $resp = $client->api('repo')->contents()->show($repoOwner, $repoName, $path, $ref);

    // Save in key, value format
    $files = [];
    foreach ($resp as $item) {
        if ($item['type'] === "dir") {
            // If this is a dir, explore it through recursion
            $item['name']['contents'] = getAllGHFiles($client, $repoOwner, $repoName, $ref, $path . "/" . $item['name']);
        }
        $files[$item['name']] = $item;
    }

    // Done
    return $files;
}