<?php

require __DIR__ . '/../vendor/autoload.php';

use App\SimpleApp;
use App\Provider\ComplexProvider as SimpleProvider;

$app = new SimpleApp(SimpleProvider::class);

/** @var SimpleProvider $provider */
$provider = $app->getProvider();

$provider->listenSaveQueueMessage();