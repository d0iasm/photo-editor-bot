<?php
require_once __DIR__ . "/vendor/autoload.php";

define("CHANNEL_ACCESS_TOKEN", 'CnOpazNl3Ns+DC9fXQckj97e0O4AAgWLZw1o6Gbym0xYMgl4gh4fIuf7k7ywc36LRCQ0gytM7hyBBepk1bfglDpgIqGO+aPlhfh3byhIi1yiqJ5vOjDs8l+hjYWhGVczYi4XIzsZYhDM1+W4y62jVwdB04t89/1O/w1cDnyilFU=');
define("CHANNEL_SECRET", '776bcf263a10cf4cb30e1f2feeb33013');

$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient(CHANNEL_ACCESS_TOKEN);
$bot = new \LINE\LINEBot($httpClient, ['channelSecret' => CHANNEL_SECRET]);

// $input = file_get_contents("php://input");
// $json = json_decode($input);
// $event = $json->events[0];

$signature = $_SERVER["HTTP_".\LINE\LINEBot\Constant\HTTPHeader::LINE_SIGNATURE];
$body = file_get_contents("php://input");
$events = $bot->parseEventRequest($body, $signature);

foreach ($events as $event) {
    if ($event instanceof \LINE\LINEBot\Event\MessageEvent\TextMessage) {
        $reply_token = $event->getReplyToken();
        $text = $event->getText();
        $bot->replyText($reply_token, $text);
    }
}

echo "OK";

// $textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder('hello world');
// $response = $bot->replyMessage('<replyToken>', $textMessageBuilder);

// $response = $bot->getMessageContent('<messageId>');
// if ($response->isSucceeded()) {
//     $tempfile = tmpfile();
//     fwrite($tempfile, $response->getRawBody());
// } else {
//     error_log($response->getHTTPStatus() . ' ' . $response->getRawBody());
// }

echo $response->getHTTPStatus() . ' ' . $response->getRawBody();

// $response = $bot->replyMessage($event->replyToken, $textMessageBuilder);
// syslog(LOG_EMERG, print_r($event->replyToken, true));
// syslog(LOG_EMERG, print_r($response, true));

?>