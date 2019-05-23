<?php

require __DIR__ . '/../vendor/autoload.php';

use App\SimpleApp;
use App\Provider\Md5Provider as Provider;

$app = new SimpleApp(Provider::class);

//on index
$app->addRoute(function (string $method, string $path, array $params) use ($app) {
    return 'GET' == $method && '/' == $path && !isset($params['url']);
}, function () {
    echo <<<HTML
<form>
<label>URL to SHORT CODE <input type="text" name="url" required></label>
<button type="submit">OK</button>
</form>
HTML;
});

//on url passed
$app->addRoute(function (string $method, string $path, array $params) use ($app) {
    if (in_array($method, ['GET', 'POST']) && '/' == $path && isset($params['url'])) {
        if (filter_var($params['url'], FILTER_VALIDATE_URL)) {
            return $params['url'];
        }

        $app->terminate(400, 'Bad Request');
    }

    return false;
}, function (string $url) use ($app) {
    $code = $app->getProvider()->getShortCodeByUrl($url);
    echo <<<HTML
<table>
    <tr><td>URL</td><td>$url</td></tr>    
    <tr><td>PROVIDER</td><td>{$app->getProviderClass()}</td></tr>    
    <tr><td>SHORT CODE</td><td>{$code}</td></tr>    
    <tr><td>LINK</td><td><a href="/$code" target="_blank">/$code</a></td></tr>    
</table>
<a href="/">go to index</a>
HTML;
});

//on shortCode passed
$app->addRoute(function (string $method, string $path) use ($app) {
    if ('GET' == $method) {
        $peaces = explode('/', trim($path, '/'));

        if (1 == count($peaces)) {
            if ($app->getProvider()->matchShortCode($peaces[0])) {
                return $peaces[0];
            }

            $app->terminate(400, 'Bad Request');
        }

        $app->terminate(404, 'Not Found');
    }

    return false;
}, function (string $shortCode) use ($app) {
    $url = $app->getProvider()->getUrlByShortCode($shortCode);
    header('Location: ' . $url, true, 302);
});

//default
$app->addRoute(function () {
    return true;
}, function () use ($app) {
    $app->terminate(404, 'Not Found');
});

$app->run();