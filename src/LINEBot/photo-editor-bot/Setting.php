<?php
namespace LINE\LINEBot\photo-editor-bot;

define("CHANNEL_ACCESS_TOKEN", 'CnOpazNl3Ns+DC9fXQckj97e0O4AAgWLZw1o6Gbym0xYMgl4gh4fIuf7k7ywc36LRCQ0gytM7hyBBepk1bfglDpgIqGO+aPlhfh3byhIi1yiqJ5vOjDs8l+hjYWhGVczYi4XIzsZYhDM1+W4y62jVwdB04t89/1O/w1cDnyilFU=');
define("CHANNEL_SECRET", '776bcf263a10cf4cb30e1f2feeb33013');

class Setting
{
    public static function getSetting()
    {
        return [
            'settings' => [
                'displayErrorDetails' => true, // set to false in production
                'logger' => [
                    'name' => 'slim-app',
                    'path' => __DIR__ . '/../../../logs/app.log',
                ],
                'bot' => [
                    'channelToken' => getenv('LINEBOT_CHANNEL_TOKEN') ?: CHANNEL_ACCESS_TOKEN,
                    'channelSecret' => getenv('LINEBOT_CHANNEL_SECRET') ?: CHANNEL_SECRET
                ],
                'apiEndpointBase' => getenv('LINEBOT_API_ENDPOINT_BASE'),
            ],
        ];
    }
}
?>