<?php

require __DIR__ . '/../vendor/autoload.php';

use App\SimpleApp;

use App\Provider\IdProvider;
use App\Provider\Md5Provider;
use App\Provider\ComplexProvider;

use App\Provider\ComplexProvider as SimpleProvider;

$app = new SimpleApp($_GET['provider'] ?? SimpleProvider::class);

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
    <tr><td>LINK</td><td><a href="/$code?provider={$app->getProviderClass()}" target="_blank">/$code</a></td></tr>    
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

            return false;
        }

        $app->terminate(404, 'Not Found');
    }

    return false;
}, function (string $shortCode) use ($app) {
    $url = $app->getProvider()->getUrlByShortCode($shortCode);

    if ($url) {
        header('Location: ' . $url, true, 302);
    } else {
        $app->terminate(404, 'Not Found');
    }
});

//bench
$app->addRoute(function (string $method, string $path) {
    return 'GET' == $method && '/bench' == $path;
}, function () use ($app) {
    foreach ([
                 IdProvider::class,
                 Md5Provider::class,
                 ComplexProvider::class
             ] as $provider) {
        $start = microtime(true);

        for ($i = 0, $l = 100; $i < $l; $i++) {
            file_get_contents('http://172.21.0.11/?provider=' . urlencode($provider) . '&url=' . urlencode('http://qwe.com/' . $i));
        }

        for ($i = 0, $l = 1000; $i < $l; $i++) {
            file_get_contents('http://172.21.0.11:81/' . md5('http://qwe.com/' . $i) . '?provider=' . urlencode($provider));
        }

        echo $provider . ':';
        echo '<br>';
        echo (microtime(true) - $start) . 'sec';
        echo '<br>';
        echo '<br>';
    }
});

//php
$app->addRoute(function (string $method, string $path) {
    return 'GET' == $method && '/php' == $path;
}, function () use ($app) {
    phpinfo();
});

//default
$app->addRoute(function () {
    return true;
}, function () use ($app) {
    $app->terminate(404, 'Not Found');
});

$app->run();