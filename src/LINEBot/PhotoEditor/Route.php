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
use LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselColumnTemplateBuilder;
use LINE\LINEBot\TemplateActionBuilder;
use LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder;

$filtertype = IMG_FILTER_GRAYSCALE;

function setFiltertype($filterName) {
  if ($filterName == 'mono') {
    $filtertype = IMG_FILTER_GRAYSCALE;
  }else if ($filterName == 'nega') {
    $filtertype = IMG_FILTER_NEGATE;
  }else if ($filterName == 'edge') {
    $filtertype = IMG_FILTER_EDGEDETECT;
  }else if ($filterName == 'removal') {
    $filtertype = IMG_FILTER_MEAN_REMOVAL;
  }else if ($filterName == 'emboss') {
    $filtertype = IMG_FILTER_EMBOSS;
  }
}

function edit($originImage) {
  ob_start();
  imagefilter($originImage, $filtertype);
  imagejpeg($originImage);
  $editedImage = ob_get_contents();
  ob_end_clean();
  return $editedImage;
}

function resize($max, $width, $height, $originImage) {
  if ($max/$width > $max/$height) {
    $ratio = $max/$height;
  } else {
    $ratio = $max/$width;
  }
  ob_start();
  $resizedImage = imagecreatetruecolor((int)$width*$ratio, (int)$height*$ratio);
  ImageCopyResampled($resizedImage, $originImage, 0, 0, 0, 0, (int)$width*$ratio, (int)$height*$ratio, $width, $height);
  imagejpeg($resizedImage);
  $resizedImage = ob_get_contents();
  ob_end_clean();
  return $resizedImage;
}

class Route {
    public $filtertype = IMG_FILTER_GRAYSCALE;

    public function register(\Slim\App $app) {
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
                        $upload = $s3->upload($bucket, 'upload/raw_image.jpg', $tempFile, 'public-read');

                        $originFilename = "https://s3-ap-northeast-1.amazonaws.com/photo-editor-bot/upload/raw_image.jpg";
                        $originImage = imagecreatefromjpeg($originFilename);
                        list($width, $height, $type, $attr) = getimagesize($originFilename);

                        $editedImage = edit($originImage);

                        if (1024 < $height || 1024 < $width) {
                          // XXX: 1024px以上の画像のリサイズを行うと真っ黒な画像になる
                          $resizedImage = resize(240, $width, $height, $editedImage);
                          // $editedImage = resize(1024, $width, $height, $editedImage);
                          $upload = $s3->upload($bucket, 'upload/resized_image.jpg', $resizedImage, 'public-read');
                          $upload = $s3->upload($bucket, 'upload/edited_image.jpg', $editedImage, 'public-read');
                        } else if (240 < $height || 240 < $width) {
                          $resizedImage = resize(240, $width, $height, $editedImage);
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
                      $act1 = new MessageTemplateActionBuilder($GLOBALS['filtertype'], IMG_FILTER_EMBOSS);
                      $act2 = new MessageTemplateActionBuilder('emboss', 'emboss');
                      $mono = new CarouselColumnTemplateBuilder('mono', 'モノクロ画像にする', 'https://s3-ap-northeast-1.amazonaws.com/photo-editor-bot/mono.jpg', [$act1, $act2]);
                      $mono2 = new CarouselColumnTemplateBuilder('モノクロ', 'mono', 'https://s3-ap-northeast-1.amazonaws.com/photo-editor-bot/mono.jpg', [$act1, $act2]);
                      $mono3 = new CarouselColumnTemplateBuilder('モノクロ', 'mono', 'https://s3-ap-northeast-1.amazonaws.com/photo-editor-bot/mono.jpg', [$act1, $act2]);
                      $template = new CarouselTemplateBuilder([$mono, $mono2, $mono3]);
                      $templateMessage = new TemplateMessageBuilder('どんな加工にするか調整できます。', $template);
                      $bot->replyMessage($event->getReplyToken(), $templateMessage);
                    }else if(strpos($getText, 'emboss') !== false){
                      setFiltertype('emboss');
                    }
                }
            }
            $res->write('OK');
            return $res;
        });
    }
}
