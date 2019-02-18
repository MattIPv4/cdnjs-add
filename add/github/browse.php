<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/github/utils.php';

$client = getGHClient();
$repo = $client->api('repo')->show($repoOwner, $repoName);

jsonify(getAllGHFiles($client, $repoOwner, $repoName, $repoRef));