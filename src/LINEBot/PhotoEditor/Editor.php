<?php
namespace LINE\LINEBot\PhotoEditor;

class Editor
{
  private static $instance = null;

  private $filtertype = IMG_FILTER_GRAYSCALE;

  private $testNum = 1;

  private function __construct() {}

  public function setNum($num){
    $s3 = \Aws\S3\S3Client::factory();
    $bucket = getenv('S3_BUCKET')?: die('No "S3_BUCKET" config var in found in env!');

    $result = $s3->putObject(array(
      'Bucket' => $bucket,
      'Key'    => 'data/num.txt',
      'Body'   => strval($num),
      'ACL'    => 'public-read'
    ));
  }

  public function getNum(){
    $s3 = \Aws\S3\S3Client::factory();
    $bucket = getenv('S3_BUCKET')?: die('No "S3_BUCKET" config var in found in env!');

    $result = $s3->getObject(array(
      'Bucket' => $bucket,
      'Key'    => 'data/num.txt'
    ));

    return $result['Body'];
  }

  public function setFiltertype($filterName) {
    if (strpos($filterName, 'gray') !== false) {
      $this->filtertype = IMG_FILTER_GRAYSCALE;
    }else if (strpos($filterName, 'nega') !== false) {
      $this->filtertype = IMG_FILTER_NEGATE;
    }else if (strpos($filterName, 'edge') !== false) {
      $this->filtertype = IMG_FILTER_EDGEDETECT;
    }else if (strpos($filterName, 'removal') !== false) {
      $this->filtertype = IMG_FILTER_MEAN_REMOVAL;
    }else if (strpos($filterName, 'emboss') !== false) {
      $this->filtertype = IMG_FILTER_EMBOSS;
    }
    return $this->filtertype;
  }

  public function getFiltertype() {
    return $this->filtertype;
  }

  public function edit($originImage) {
    ob_start();
    imagefilter($originImage, $this->getFiltertype());
    imagejpeg($originImage);
    $editedImage = ob_get_contents();
    ob_end_clean();
    return $editedImage;
  }

  public function resize($max, $width, $height, $originImage) {
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

  public static function getInstance() {
    if (is_null(self::$instance)) {
      self::$instance = new self();
    }
    return self::$instance;
  }
}
