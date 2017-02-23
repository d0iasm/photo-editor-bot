<?php
namespace LINE\LINEBot\PhotoEditor;

use LINE\LINEBot;
use LINE\LINEBot\Constant\HTTPHeader;
use LINE\LINEBot\Event\MessageEvent;
use LINE\LINEBot\Event\MessageEvent\TextMessage;
use LINE\LINEBot\Event\MessageEvent\StickerMessage;
use LINE\LINEBot\Event\MessageEvent\ImageMessage;
use LINE\LINEBot\Exception\InvalidEventRequestException;
use LINE\LINEBot\Exception\InvalidSignatureException;
use LINE\LINEBot\Exception\UnknownEventTypeException;
use LINE\LINEBot\Exception\UnknownMessageTypeException;
use LINE\LINEBot\MessageBuilder;
use LINE\LINEBot\MessageBuilder\ImageMessageBuilder;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;

class Route
{
    public function register(\Slim\App $app)
    {
        $app->post('/callback', function (\Slim\Http\Request $req, \Slim\Http\Response $res) {
            /** @var \LINE\LINEBot $bot */
            $bot = $this->bot;
            /** @var \Monolog\Logger $logger */
            $logger = $this->logger;
            $signature = $req->getHeader(HTTPHeader::LINE_SIGNATURE);
            if (empty($signature)) {
                return $res->withStatus(400, 'Bad Request');
            }
            // Check request with signature and parse request
            try {
                $events = $bot->parseEventRequest($req->getBody(), $signature[0]);
            } catch (InvalidSignatureException $e) {
                return $res->withStatus(400, 'Invalid signature');
            } catch (UnknownEventTypeException $e) {
                return $res->withStatus(400, 'Unknown event type has come');
            } catch (UnknownMessageTypeException $e) {
                return $res->withStatus(400, 'Unknown message type has come');
            } catch (InvalidEventRequestException $e) {
                return $res->withStatus(400, "Invalid event request");
            }

            foreach ($events as $event) {
                if (!($event instanceof MessageEvent)) {
                    $logger->info('Non message event has come');
                    continue;
                }

                if($event instanceof ImageMessage){
                    // $replyText = new TextMessageBuilder('スタンプだ');
                    $replyImage = $bot->getMessageContent($event->getMessageId());
                    
                    $response = $bot->replyMessage($event->getReplyToken(), $replyImage);

                    // $replyText = new TextMessageBuilder($event->getText());
                    // $eventType = $event->getMessageType();
                    $logger->info('Reply text: ' . $replyText);
                    $resp = $bot->replyText($event->getReplyToken(), $replyText);
                    $bot->replyText($event->getReplyToken(), $eventType);
                    $logger->info($resp->getHTTPStatus() . ': ' . $resp->getRawBody());
                }else if($event instanceof TextMessage) {
                    $replyText = $event->getText();
                    $bot->replyMessage($event->getReplyToken(), $replyText);
                    // $bot->replyText($event->getReplyToken(), $replyText);
                }else{
                    $replyText = new TextMessageBuilder('画像を送ってね。詳しい使い方はメニューまたは Help と送信');
                    $bot->replyMessage($event->getReplyToken(), $replyText);
                }
            }
            $res->write('OK');
            return $res;
        });
    }
}
