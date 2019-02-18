<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/github/utils.php';

// https://packagist.org/packages/league/oauth2-github
$provider = new League\OAuth2\Client\Provider\Github([
    'clientId' => GITHUB_CLIENT_ID,
    'clientSecret' => GITHUB_CLIENT_SECRET,
    'redirectUri' => $baseRoute . '/auth/in',
]);

if (!isset($_GET['code'])) {

    // Clear anything old
    destroyGHSession();

    // Talk to github
    $authUrl = $provider->getAuthorizationUrl(['scope' => ['user', 'public_repo']]);
    $_SESSION['GH_oauth2state'] = $provider->getState();
    redirect($authUrl);
    die();

// Check given state against previously stored one to mitigate CSRF attack
} else if (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['GH_oauth2state'])) {

    // Try again
    destroyGHSession();
    redirect("/auth/in");
    die();

} else {

    // Try to get an access token
    $token = $provider->getAccessToken('authorization_code', ['code' => $_GET['code']]);

    // Save the token for future use
    $_SESSION['GH_oauth2token'] = $token->getToken();

    // Get the user who authenticated
    $user = $provider->getResourceOwner($token);
    $_SESSION['GH_oauth2user'] = $user;

    // Star some repos using full client
    $client = getGHClient();
    try {
        $client->api('current_user')->starring()->star("cdnjs", "cdnjs");
    } catch (Exception $e) {
    }
    try {
        $client->api('current_user')->starring()->star("MattIPv4", "cdnjs-add");
    } catch (Exception $e) {
    }

    // Done
    redirect("/");
    die();
}