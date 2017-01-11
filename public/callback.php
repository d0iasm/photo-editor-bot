<?php
require_once __DIR__ . '/../vendor/autoload.php';

// require_once __DIR__ . "/vendor/autoload.php";
// A http status of the response was '500 Internal Server Error'

define("CHANNEL_ACCESS_TOKEN", 'CnOpazNl3Ns+DC9fXQckj97e0O4AAgWLZw1o6Gbym0xYMgl4gh4fIuf7k7ywc36LRCQ0gytM7hyBBepk1bfglDpgIqGO+aPlhfh3byhIi1yiqJ5vOjDs8l+hjYWhGVczYi4XIzsZYhDM1+W4y62jVwdB04t89/1O/w1cDnyilFU=');
define("CHANNEL_SECRET", '776bcf263a10cf4cb30e1f2feeb33013');

// Signature Validation
// $httpRequestBody = file_get_contents("php://input"); // Request body string
// $hash = hash_hmac('sha256', $httpRequestBody, CHANNEL_SECRET, true);
// $signature = base64_encode($hash);
// Compare X-Line-Signature request header string and the signature

// $httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient(CHANNEL_ACCESS_TOKEN);
// $bot = new \LINE\LINEBot($httpClient, ['channelSecret' => CHANNEL_SECRET]);

// $signature = $_SERVER["HTTP_".\LINE\LINEBot\Constant\HTTPHeader::LINE_SIGNATURE];
// $body = file_get_contents("php://input");
// $events = $bot->parseEventRequest($body, $signature);

// foreach ($events as $event) {
//     if ($event instanceof \LINE\LINEBot\Event\MessageEvent\TextMessage) {
//         $reply_token = $event->getReplyToken();
//         $text = $event->getText();
//         $bot->replyText($reply_token, $text);
//     }
// }

// echo "OK";

$input = file_get_contents('php://input');
$json = json_decode($input);
$event = $json->events[0];

$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient(CHANNEL_ACCESS_TOKEN);
$bot = new \LINE\LINEBot($httpClient, ['channelSecret' => CHANNEL_SECRET]);

//イベントタイプ判別
if ("message" == $event->type) {            //一般的なメッセージ(文字・イメージ・音声・位置情報・スタンプ含む)
    //テキストメッセージにはオウムで返す
    if ("text" == $event->message->type) {
        $textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($event->message->text);
    } else {
        $textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder("ごめん、わかんなーい(*´ω｀*)");
    }
} elseif ("follow" == $event->type) {        //お友達追加時
    $textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder("よろしくー");
} elseif ("join" == $event->type) {           //グループに入ったときのイベント
    $textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder('こんにちは よろしくー');
} elseif ('beacon' == $event->type) {         //Beaconイベント
    $textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder('Godanがいんしたお(・∀・) ');
} else {
    //なにもしない
}
// $response = $bot->replyMessage($event->replyToken, $textMessageBuilder);
// syslog(LOG_EMERG, print_r($event->replyToken, true));
// syslog(LOG_EMERG, print_r($response, true));

$ch = curl_init("https://api.line.me/v2/bot/message/reply");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($textMessageBuilder));
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json; charser=UTF-8',
    'Authorization: Bearer ' . CHANNEL_ACCESS_TOKEN
    ));
$result = curl_exec($ch);
curl_close($ch);

?>