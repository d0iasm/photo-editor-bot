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
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ButtonTemplateBuilder;
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
                    if(strpos($getText, '> 加工の調整をする') !== false){
                      $commonAct = new MessageTemplateActionBuilder('変更しない', '> 変更しない');

                      $brightAct = new MessageTemplateActionBuilder('このフィルターを使う', '> 輝度を変更する');
                      $bright = new CarouselColumnTemplateBuilder('bright', '輝度を変更する', 'https://s3-ap-northeast-1.amazonaws.com/photo-editor-bot/bright.jpg', [$brightAct, $commonAct]);

                      $blurAct = new MessageTemplateActionBuilder('このフィルターを使う', '> 画像をぼかす');
                      $blur = new CarouselColumnTemplateBuilder('blur', '画像をぼかす', 'https://s3-ap-northeast-1.amazonaws.com/photo-editor-bot/blur.jpg', [$blurAct, $commonAct]);

                      $sketchAct = new MessageTemplateActionBuilder('このフィルターを使う', '> スケッチ風にする');
                      $sketch = new CarouselColumnTemplateBuilder('sketch', 'スケッチ風にする', 'https://s3-ap-northeast-1.amazonaws.com/photo-editor-bot/sketch.jpg', [$sketchAct, $commonAct]);

                      $pixelateAct = new MessageTemplateActionBuilder('このフィルターを使う', '> モザイクをかける');
                      $pixelate = new CarouselColumnTemplateBuilder('pixelate', 'モザイクをかける', 'https://s3-ap-northeast-1.amazonaws.com/photo-editor-bot/pixelate.jpg', [$pixelateAct, $commonAct]);

                      $monoAct = new MessageTemplateActionBuilder('このフィルターを使う', '> モノクロ画像にする');
                      $mono = new CarouselColumnTemplateBuilder('mono', 'モノクロ画像にする', 'https://s3-ap-northeast-1.amazonaws.com/photo-editor-bot/mono.jpg', [$monoAct, $commonAct]);

                      $template = new CarouselTemplateBuilder([$bright, $blur, $sketch, $pixelate, $mono]);
                      $templateMessage = new TemplateMessageBuilder('どんな加工にするか調整できます', $template);
                      $bot->replyMessage($event->getReplyToken(), $templateMessage);

                    }else if(strpos($getText, '> 輝度を変更する') !== false){
                      //
                      $brightAct1 = new MessageTemplateActionBuilder('高輝度', '> 輝度を高くする');
                      $brightAct2 = new MessageTemplateActionBuilder('中輝度', '> 輝度を戻す');
                      $brightAct3 = new MessageTemplateActionBuilder('低輝度', '> 輝度を低くする');
                      $brightDetail = new ButtonTemplateBuilder('bright', '明るさの調整ができます', 'https://s3-ap-northeast-1.amazonaws.com/photo-editor-bot/bright.jpg', [$brightAct1, $brightAct2, $brightAct3]);
                      $templateMessage = new TemplateMessageBuilder('明るさの調節ができます', $brightDetail);
                      $bot->replyMessage($event->getReplyToken(), $templateMessage);

                    }else if(strpos($getText, '> 画像をぼかす') !== false){

                      $editor->setFiltertype('blur', -999);
                      $replyText = new TextMessageBuilder('blur加工に変更しました');
                      $bot->replyMessage($event->getReplyToken(), $replyText);

                    }else if(strpos($getText, '> スケッチ風にする') !== false){

                      $editor->setFiltertype('sketch', -999);
                      $replyText = new TextMessageBuilder('sketch加工に変更しました');
                      $bot->replyMessage($event->getReplyToken(), $replyText);

                    }else if(strpos($getText, '> モザイクをかける') !== false){

                      $pixelateAct1 = new MessageTemplateActionBuilder('細かく', '> モザイクを細かくする');
                      $pixelateAct2 = new MessageTemplateActionBuilder('大きく', '> モザイクを大きくする');
                      $pixelateDetail = new ButtonTemplateBuilder('pixelate', 'モザイクの大きさの調整ができます', 'https://s3-ap-northeast-1.amazonaws.com/photo-editor-bot/pixelate.jpg', [$pixelateAct1, $pixelateAct2]);
                      $templateMessage = new TemplateMessageBuilder('モザイクの大きさの調整ができます', $pixelateDetail);
                      $bot->replyMessage($event->getReplyToken(), $templateMessage);

                    }else if(strpos($getText, '> モノクロ画像にする') !== false){

                      $editor->setFiltertype('mono', -999);
                      $replyText = new TextMessageBuilder('mono加工に変更しました');
                      $bot->replyMessage($event->getReplyToken(), $replyText);

                    }else if(strpos($getText, '> 輝度を高くする') !== false){

                      $editor->setFiltertype('bright', 50);
                      $replyText = new TextMessageBuilder('bright加工を明るくしました');
                      $bot->replyMessage($event->getReplyToken(), $replyText);

                    }else if(strpos($getText, '> 輝度を戻す') !== false){

                      $editor->setFiltertype('bright', 0);
                      $replyText = new TextMessageBuilder('bright加工を戻しました');
                      $bot->replyMessage($event->getReplyToken(), $replyText);

                    }else if(strpos($getText, '> 輝度を低くする') !== false){

                      $editor->setFiltertype('bright', -50);
                      $replyText = new TextMessageBuilder('bright加工を暗くしました');
                      $bot->replyMessage($event->getReplyToken(), $replyText);

                    }else if(strpos($getText, '> モザイクを細かくする') !== false){

                      $editor->setFiltertype('pixelate', 3);
                      $replyText = new TextMessageBuilder('pixelate加工を細かくしました');
                      $bot->replyMessage($event->getReplyToken(), $replyText);

                    }else if(strpos($getText, '> モザイクを大きくする') !== false){

                      $editor->setFiltertype('pixelate', 15);
                      $replyText = new TextMessageBuilder('pixelate加工を大きくしました');
                      $bot->replyMessage($event->getReplyToken(), $replyText);

                    }
                }
            }
            $res->write('OK');
            return $res;
        });
    }
}
