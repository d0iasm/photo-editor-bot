<?php
define("CHANNEL_ACCESS_TOKEN", 'CnOpazNl3Ns+DC9fXQckj97e0O4AAgWLZw1o6Gbym0xYMgl4gh4fIuf7k7ywc36LRCQ0gytM7hyBBepk1bfglDpgIqGO+aPlhfh3byhIi1yiqJ5vOjDs8l+hjYWhGVczYi4XIzsZYhDM1+W4y62jVwdB04t89/1O/w1cDnyilFU=');
define("CHANNEL_SECRET", '776bcf263a10cf4cb30e1f2feeb33013');

$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient(CHANNEL_ACCESS_TOKEN);
$bot = new \LINE\LINEBot($httpClient, ['channelSecret' => CHANNEL_SECRET]);

$input = file_get_contents('php://input');
$json = json_decode($input);
$event = $json->events[0];

$textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder('hello');
$response = $bot->replyMessage($event->replyToken, $textMessageBuilder);
if ($response->isSucceeded()) {
    echo 'Succeeded!';
    return;
}

echo $response->getHTTPStatus . ' ' . $response->getRawBody();

?>