<?php
namespace LINE\LINEBot\PhotoEditor;

use LINE\LINEBot;
use LINE\LINEBot\Constant\HTTPHeader;
use LINE\LINEBot\Event\MessageEvent;
use LINE\LINEBot\Event\MessageEvent\TextMessage;
use LINE\LINEBot\Event\MessageEvent\ImageMessage;
use LINE\LINEBot\Exception\InvalidEventRequestException;
use LINE\LINEBot\Exception\InvalidSignatureException;
use LINE\LINEBot\Exception\UnknownEventTypeException;
use LINE\LINEBot\Exception\UnknownMessageTypeException;
use LINE\LINEBot\MessageBuilder;
use LINE\LINEBot\MessageBuilder\ImageMessageBuilder;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use LINE\LINEBot\MessageBuilder\TemplateMessageBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ConfirmTemplateBuilder;
use LINE\LINEBot\TemplateActionBuilder;
use LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder;

require_once __DIR__ . '/../vendor/autoload.php';

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
                    $binaryImage = $bot->getMessageContent($event->getMessageId());

                    $s3 = Aws\S3\S3Client::factory();
                    $bucket = getenv('S3_BUCKET')?: die('No "S3_BUCKET" config var in found in env!');

                    // try {
                    //   $upload = $s3->upload($bucket, 'hogeFile.jpg', 'hogehoge', 'public-read');
                    //   $upload = $s3->upload($bucket, 'hogeFile.jpg', fopen($_FILES['userfile']['tmp_name'], 'rb'), 'public-read');
                    //   $upload->get('ObjectURL')
                    // } catch(Exception $e) {
                    //   $errorText = new TextMessageBuilder('[ERROR] 画像の取得に失敗しました。');
                    //   $bot->replyMessage($event->getReplyToken(), $errorText);
                    // }

                    $editedImage = new ImageMessageBuilder('https://s3-ap-northeast-1.amazonaws.com/photo-editor-bot/150x150.jpg', 'https://s3-ap-northeast-1.amazonaws.com/photo-editor-bot/150x150.jpg');
                    $bot->replyMessage($event->getReplyToken(), $editedImage);

                }else if($event instanceof TextMessage) {
                    $getText = $event->getText();
                    if($getText == 'help' || $getText == 'Help' || $getText == 'HELP' || $getText == 'ヘルプ'){
                      $act1 = new MessageTemplateActionBuilder('labelHoge1', 'textHoge1');
                      $act2 = new MessageTemplateActionBuilder('labelHoge2', 'textHoge2');
                      $template = new ConfirmTemplateBuilder('tempHoge', [$act1, $act2]);
                      $templateMessage = new TemplateMessageBuilder('tempMsgHoge', $template);
                      $bot->replyMessage($event->getReplyToken(), $templateMessage);
                    }else{
                      $replyText = new TextMessageBuilder('画像を送ってね。詳しい使い方はメニュー、または Help と送信');
                      $bot->replyMessage($event->getReplyToken(), $replyText);
                    }
                }else{
                    $replyText = new TextMessageBuilder('画像を送ってね。詳しい使い方はメニュー、または Help と送信');
                    $bot->replyMessage($event->getReplyToken(), $replyText);
                }
            }
            $res->write('OK');
            return $res;
        });
    }
}
