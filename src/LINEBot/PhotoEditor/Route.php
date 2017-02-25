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

class Route
{
    public function register(\Slim\App $app)
    {
        $app->post('/callback', function (\Slim\Http\Request $req, \Slim\Http\Response $res) {

            $bot = $this->bot;

            $logger = $this->logger;
            $signature = $req->getHeader(HTTPHeader::LINE_SIGNATURE);
            if (empty($signature)) {
                return $res->withStatus(400, 'Bad Request');
            }

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

                    $s3 = \Aws\S3\S3Client::factory();
                    $bucket = getenv('S3_BUCKET')?: die('No "S3_BUCKET" config var in found in env!');

                    $binaryImage = $bot->getMessageContent($event->getMessageId());
                    if ($binaryImage->isSucceeded()) {
                      $tempFile = tmpfile();
                      fwrite($tempFile, $binaryImage->getRawBody());

                      try {
                        $upload = $s3->upload($bucket, 'raw_image.jpg', $tempFile, 'public-read');

                        $originFilename = "https://s3-ap-northeast-1.amazonaws.com/photo-editor-bot/raw_image.jpg";
                        $originImage = imagecreatefromjpeg($originFilename);
                        list($width, $height, $type, $attr) = getimagesize($originFilename);
                        $bot -> replyMessage($event->getReplyToken(), new TextMessageBuilder($width));

                        if (240 < $height || 240 < $width) {
                          if (240/$height < 240/$width) {
                            $ratio = 240/$height;
                          } else {
                            $ratio = 240/$width;
                          }
                          $resizedImage = imagecreatetruecolor((int)$width*$ratio, (int)$height*$ratio);
                          ImageCopyResampled($resizedImage, $originImage, 0, 0, 0, 0, (int)$width*$ratio, (int)$height*$ratio, $width, $height);
                        }

                        ob_start();
                        imagejpeg($originImage);
                        imagejpeg($resizedImage);
                        $ei = ob_get_contents();
                        $resizedImage = ob_get_contents();
                        ob_end_clean();

                        $upload = $s3->upload($bucket, 'black.jpg', $ei, 'public-read');
                        $upload = $s3->upload($bucket, 'resized_image.jpg', $resizedImage, 'public-read');

                        // $uploadURL = new TextMessageBuilder($upload->get('ObjectURL'));
                        // $bot->replyMessage($event->getReplyToken(), $uploadURL);

                        // exec('python ../../python/filter.py');

                        // $editedImage = new ImageMessageBuilder('https://s3-ap-northeast-1.amazonaws.com/photo-editor-bot/raw_image.jpg', 'https://s3-ap-northeast-1.amazonaws.com/photo-editor-bot/150x150.jpg');
                        // $editedImage = new ImageMessageBuilder('https://s3-ap-northeast-1.amazonaws.com/photo-editor-bot/edited_image.jpg', 'https://s3-ap-northeast-1.amazonaws.com/photo-editor-bot/resized_image.jpg');
                        // $bot->replyMessage($event->getReplyToken(), $editedImage);

                      } catch(\Aws\S3\Exception\S3Exception $e) {
                        $errorText = new TextMessageBuilder($e->getMessage());
                        $bot->replyMessage($event->getReplyToken(), $errorText);
                      }

                    } else {
                      error_log($binaryImage->getHTTPStatus() . ' ' . $binaryImage->getRawBody());
                    }

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
