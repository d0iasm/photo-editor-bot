<?php
namespace LINE\LINEBot\PhotoEditor;

use LINE\LINEBot;
use LINE\LINEBot\PhotoEditor\Editor;
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
use LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselColumnTemplateBuilder;
use LINE\LINEBot\TemplateActionBuilder;
use LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder;

class Route
{
    public function register(\Slim\App $app)
    {
        $app->post('/callback', function (\Slim\Http\Request $req, \Slim\Http\Response $res) {

            $bot = $this->bot;
            $editor = Editor::getInstance();
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
                        $upload = $s3->upload($bucket, 'upload/raw_image.jpg', $tempFile, 'public-read');

                        $originFilename = "https://s3-ap-northeast-1.amazonaws.com/photo-editor-bot/upload/raw_image.jpg";
                        $originImage = imagecreatefromjpeg($originFilename);
                        list($width, $height, $type, $attr) = getimagesize($originFilename);

                        $editedImage = $editor->edit($originImage);

                        if (1024 < $height || 1024 < $width) {
                          // XXX: 1024px以上の画像のリサイズを行うと真っ黒な画像になる
                          $resizedImage = $editor->resize(240, $width, $height, $editedImage);
                          // $editedImage = $editor->resize(1024, $width, $height, $editedImage);
                          $upload = $s3->upload($bucket, 'upload/resized_image.jpg', $resizedImage, 'public-read');
                          $upload = $s3->upload($bucket, 'upload/edited_image.jpg', $editedImage, 'public-read');
                        } else if (240 < $height || 240 < $width) {
                          $resizedImage = $editor->resize(240, $width, $height, $editedImage);
                          $upload = $s3->upload($bucket, 'upload/resized_image.jpg', $resizedImage, 'public-read');
                          $upload = $s3->upload($bucket, 'upload/edited_image.jpg', $editedImage, 'public-read');
                        } else {
                          $upload = $s3->upload($bucket, 'upload/resized_image.jpg', $editedImage, 'public-read');
                          $upload = $s3->upload($bucket, 'upload/edited_image.jpg', $editedImage, 'public-read');
                        }

                        $editedImage = new ImageMessageBuilder('https://s3-ap-northeast-1.amazonaws.com/photo-editor-bot/upload/edited_image.jpg', 'https://s3-ap-northeast-1.amazonaws.com/photo-editor-bot/upload/resized_image.jpg');
                        $bot->replyMessage($event->getReplyToken(), $editedImage);

                      } catch(\Aws\S3\Exception\S3Exception $e) {
                        $errorText = new TextMessageBuilder($e->getMessage());
                        $bot->replyMessage($event->getReplyToken(), $errorText);
                      }
                    } else {
                      error_log($binaryImage->getHTTPStatus() . ' ' . $binaryImage->getRawBody());
                    }

                }else if($event instanceof TextMessage) {
                    $getText = $event->getText();
                    if(strpos($getText, '加工の調整をする') !== false){
                      $act1 = new MessageTemplateActionBuilder(var_dump($editor->getFiltertype()), IMG_FILTER_NEGATE);
                      $act2 = new MessageTemplateActionBuilder('emboss', 'emboss');
                      $mono = new CarouselColumnTemplateBuilder('mono', 'モノクロ画像にする', 'https://s3-ap-northeast-1.amazonaws.com/photo-editor-bot/mono.jpg', [$act1, $act2]);
                      $nega = new CarouselColumnTemplateBuilder('nega', '色を反転させる', 'https://s3-ap-northeast-1.amazonaws.com/photo-editor-bot/nega.jpg', [$act1, $act2]);
                      $removal = new CarouselColumnTemplateBuilder('removal', 'スケッチ風にする', 'https://s3-ap-northeast-1.amazonaws.com/photo-editor-bot/removal.jpg', [$act1, $act2]);
                      $emboss = new CarouselColumnTemplateBuilder('emboss', 'エンボス加工をする', 'https://s3-ap-northeast-1.amazonaws.com/photo-editor-bot/emboss.jpg', [$act1, $act2]);
                      $edge = new CarouselColumnTemplateBuilder('edge', 'エッジを強調する', 'https://s3-ap-northeast-1.amazonaws.com/photo-editor-bot/edge.jpg', [$act1, $act2]);
                      $template = new CarouselTemplateBuilder([$mono, $nega, $removal, $emboss, $edge]);
                      $templateMessage = new TemplateMessageBuilder('どんな加工にするか調整できます。', $template);
                      $bot->replyMessage($event->getReplyToken(), $templateMessage);
                    }else if(strpos($getText, 'emboss') !== false){
                      $replyText = new TextMessageBuilder($editor->setFiltertype('emboss'));
                      // $replyText = new TextMessageBuilder('emboss 加工に変更しました');
                      $bot->replyMessage($event->getReplyToken(), $replyText);
                    }
                }
            }
            $res->write('OK');
            return $res;
        });
    }
}
