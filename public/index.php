<?php
// use LINE\LINEBot\PhotoEditor\Dependency;
// use LINE\LINEBot\PhotoEditor\Route;
// use LINE\LINEBot\PhotoEditor\Setting;

require_once __DIR__ . '/../vendor/autoload.php';

$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient('<channel access token>');
$bot = new \LINE\LINEBot($httpClient, ['channelSecret' => '<channel secret>']);

$textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder('hello');

$bot->replyMessage('<replyToken>', $textMessageBuilder);

// $app = new \Slim\App;
// $app->post('/add/example2', function () {
    // echo "Example2.";
// });

// $app->run();

// $setting = Setting::getSetting();
// $app = new Slim\App($setting);

// (new Dependency())->register($app);
// (new Route())->register($app);

// $app->run();
