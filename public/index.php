<?php
// use LINE\LINEBot\PhotoEditor\Dependency;
// use LINE\LINEBot\PhotoEditor\Route;
// use LINE\LINEBot\PhotoEditor\Setting;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/LINEBot/PhotoEditor/Setting.php';
require_once __DIR__ . '/../src/LINEBot/PhotoEditor/Dependency.php';
require_once __DIR__ . '/../src/LINEBot/PhotoEditor/Route.php';

$config = ['settings' => [
    'addContentLengthHeader' => false,
]];
$app = new \Slim\App($config);

// Define app routes
$app->get('/hello/{name}', function ($request, $response, $args) {
    return $response->write("Hello " . $args['name']);
});

$app->run();


// $setting = Setting::getSetting();
// $app = new Slim\App($setting);

// (new Dependency())->register($app);
// (new Route())->register($app);

// $app->run();