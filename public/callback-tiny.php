<?php
define("CHANNEL_ACCESS_TOKEN", 'CnOpazNl3Ns+DC9fXQckj97e0O4AAgWLZw1o6Gbym0xYMgl4gh4fIuf7k7ywc36LRCQ0gytM7hyBBepk1bfglDpgIqGO+aPlhfh3byhIi1yiqJ5vOjDs8l+hjYWhGVczYi4XIzsZYhDM1+W4y62jVwdB04t89/1O/w1cDnyilFU=');
define("CHANNEL_SECRET", '776bcf263a10cf4cb30e1f2feeb33013');

require_once('./LINEBotTiny.php');
// require_once __DIR__ . '/../vendor/autoload.php';

$client = new LINEBotTiny(CHANNEL_ACCESS_TOKEN, CHANNEL_SECRET);

// $replyMessage = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder("hogehoge");

function replyText($event, $message){
    return array(
        'replyToken' => $event['replyToken'],
        'messages' => array(
            array(
                'type' => 'text',
                'text' => $message['text']
            ),
            array(
                'type' => 'text',
                'text' => 'ですね。'
            )
        )
    );
}

function replySticker($event, $message){
    $packageId = 1;
    $stickerId = rand(100, 139);
    return array(
        'replyToken' => $event['replyToken'],
        'messages' => array(
            array(
                'type' => 'sticker',
                'packageId' => $packageId,
                'stickerId' => $stickerId
            )
        )
    );
}

foreach ($client->parseEvents() as $event) {
    switch ($event['type']) {
        case 'message':
            $message = $event['message'];
            switch ($message['type']) {
                case 'text':
                    // $client->replyMessage(array(
                    //     'replyToken' => $event['replyToken'],
                    //     'messages' => array(
                    //         array(
                    //             'type' => 'text',
                    //             'text' => $message['text']
                    //         ),
                    //         array(
                    //             'type' => 'text',
                    //             'text' => 'ですね。'
                    //         )
                    //     )
                    // ));
                    $client->replyMessage(replyText($event, $message));
                    break;
                case 'image':
                    $client->replyMessage(array(
                        'replyToken' => $event['replyToken'],
                        'messages' => array(
                            array(
                                'type' => 'image',
                                'originalContentUrl' => 'http://3dicon-free.com/material/066.jpg',
                                'previewImageUrl' => 'http://3dicon-free.com/material/066.jpg'
                            )
                        )
                    ));
                    break;
                case 'sticker':
                    $client->replyMessage(replySticker($event, $message));
                    break;
                default:
                    error_log("Unsupporeted message type: " . $message['type']);
                    break;
            }
            break;
        default:
            error_log("Unsupporeted event type: " . $event['type']);
            break;
    }
};