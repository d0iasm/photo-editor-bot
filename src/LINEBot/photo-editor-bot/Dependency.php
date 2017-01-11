<?php
namespace LINE\LINEBot\photo-editor-bot;

use LINE\LINEBot;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;

class Dependency
{
    public function register(\Slim\App $app)
    {
        $container = $app->getContainer();
        $container['logger'] = function ($c) {
            $settings = $c->get('settings')['logger'];
            $logger = new \Monolog\Logger($settings['name']);
            $logger->pushProcessor(new \Monolog\Processor\UidProcessor());
            $logger->pushHandler(new \Monolog\Handler\StreamHandler($settings['path'], \Monolog\Logger::DEBUG));
            return $logger;
        };
        $container['bot'] = function ($c) {
            $settings = $c->get('settings');
            $channelSecret = $settings['bot']['channelSecret'];
            $channelToken = $settings['bot']['channelToken'];
            $apiEndpointBase = $settings['apiEndpointBase'];
            $bot = new LINEBot(new CurlHTTPClient($channelToken), [
                'channelSecret' => $channelSecret,
                'endpointBase' => $apiEndpointBase, // <= Normally, you can omit this
            ]);
            return $bot;
        };
    }
}
?>