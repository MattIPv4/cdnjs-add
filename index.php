<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

session_start();
session_regenerate_id();

$baseRoute = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://" . $_SERVER["HTTP_HOST"];
$route = strtok(trim($_SERVER['REQUEST_URI'], "/"), "?");
$routes = explode('/', $route . '/'); // append / to ensure final blank string
$fullRoute = $baseRoute . "/" . $route;
$clientIp = isset($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];

function redirect($url)
{
    header("HTTP/1.0 301 Moved Permanently");
    header("Location: " . $url);
    die("<script>window.location.replace('" . $url . "');</script>");
}

function render($view, $vars = [], callable $func = NULL)
{
    if (file_exists($view)) {
        extract($vars);
        extract($GLOBALS);

        ob_start();
        include($view);
        $ec = ob_get_contents();
        ob_end_clean();

        if ($func) {
            $ec = $func($ec);
        }
        if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) {
            $ec = gzencode($ec, 9);
            header('Content-Encoding: gzip');
        }
        echo $ec;

    } else {
        render("lost.php", $vars, $func);
    }
    die();
}

function isAuthed()
{
    return isset($_SESSION['GH_oauth2token']) && isset($_SESSION['GH_oauth2user']);
}

function jsonify($data)
{
    header("Content-type: application/json");
    echo json_encode($data, JSON_PRETTY_PRINT);
}

switch ($routes[0]) {

    case "auth":
        switch ($routes[1]) {
            case "in":
                render("github/oauth.php");
                break;

            case "out":
                require_once $_SERVER['DOCUMENT_ROOT'] . '/github/utils.php';
                destroyGHSession();
                session_destroy();
                redirect("/");
                break;

            default:
                redirect("/auth/in");
                break;

        }
        break;

    case "add":
        if (!isAuthed()) redirect("/auth/in");

        switch ($routes[1]) {
            case "git":
            case "github":
                if ((count($routes) == 5 || count($routes) == 6) && !empty($routes[2]) && !empty($routes[3])) {
                    render("add/github/browse.php",
                        ["repoOwner" => $routes[2], "repoName" => $routes[3], "repoRef" => $routes[4] ?? "master"]);
                    break;
                }
                render("add/github/start.php");
                break;

            default:
                redirect("/add/github");
                break;

        }
        break;

    default:
        if (!isAuthed()) redirect("/auth/in");
        echo $_SESSION['GH_oauth2user']->getNickname();
        break;
}