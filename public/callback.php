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

// $httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient(CHANNEL_ACCESS_TOKEN);
// $bot = new \LINE\LINEBot($httpClient, ['channelSecret' => CHANNEL_SECRET]);
// // A http status of the response was '500 Internal Server Error'.

// $json_string = file_get_contents('php://input');
// $jsonObj = json_decode($json_string);

// $type = $jsonObj->{"events"}[0]->{"message"}->{"type"};

// //ReplyToken取得
// $replyToken = $jsonObj->{"events"}[0]->{"replyToken"};

// //メッセージ以外のときは何も返さず終了
// if($type != "text"){
// 	exit;
// }else{
//     $text = $jsonObj->{"events"}[0]->{"message"}->{"text"};
// }

// $post_data = [
// 	"replyToken" => $replyToken,
// 	"messages" => [$response_format_text]
// ];

// $ch = curl_init("https://api.line.me/v2/bot/message/reply");
// curl_setopt($ch, CURLOPT_POST, true);
// curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
// curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($replyToken));
// curl_setopt($ch, CURLOPT_HTTPHEADER, array(
//     'Content-Type: application/json; charser=UTF-8',
//     'Authorization: Bearer ' . CHANNEL_ACCESS_TOKEN
//     ));
// $result = curl_exec($ch);
// curl_close($ch);


$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient(CHANNEL_ACCESS_TOKEN);
$bot = new \LINE\LINEBot($httpClient, ['channelSecret' => CHANNEL_SECRET]);

$input = file_get_contents('php://input');
$json = json_decode($input);
$event = $json->events[0];

$textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder('hello');
$response = $bot->replyMessage($event->replyToken, $textMessageBuilder);

echo $response->getHTTPStatus() . ' ' . $response->getRawBody();


?>