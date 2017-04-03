<?php
namespace LINE\LINEBot\PhotoEditor;

define("CHANNEL_ACCESS_TOKEN", getenv('CHANNEL_ACCESS_TOKEN'));
define("CHANNEL_SECRET", getenv('CHANNEL_SECRET'));

class Setting
{
    public static function getSetting()
    {
        return [
            'settings' => [
                'displayErrorDetails' => false, // set to false in production
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
