<?php
// use LINE\LINEBot\PhotoEditor\Dependency;
// use LINE\LINEBot\PhotoEditor\Route;
// use LINE\LINEBot\PhotoEditor\Setting;

require_once __DIR__ . '/../vendor/autoload.php';

$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient('CnOpazNl3Ns+DC9fXQckj97e0O4AAgWLZw1o6Gbym0xYMgl4gh4fIuf7k7ywc36LRCQ0gytM7hyBBepk1bfglDpgIqGO+aPlhfh3byhIi1yiqJ5vOjDs8l+hjYWhGVczYi4XIzsZYhDM1+W4y62jVwdB04t89/1O/w1cDnyilFU=');
$bot = new \LINE\LINEBot($httpClient, ['channelSecret' => '776bcf263a10cf4cb30e1f2feeb33013']);

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
