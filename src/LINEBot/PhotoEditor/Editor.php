<?php
namespace LINE\LINEBot\PhotoEditor;

class Editor
{
  private static $instance = null;
  private function __construct() {}

  public function setFilterNum($num, $arg){
    $s3 = \Aws\S3\S3Client::factory();
    $bucket = getenv('S3_BUCKET')?: die('No "S3_BUCKET" config var in found in env!');

    try {
      $numResult = $s3->putObject(array(
        'Bucket' => $bucket,
        'Key'    => 'data/filter_num.txt',
        'Body'   => strval($num),
        'ACL'    => 'public-read'
      ));
      $argResult = $s3->putObject(array(
        'Bucket' => $bucket,
        'Key'    => 'data/filter_arg.txt',
        'Body'   => strval($arg),
        'ACL'    => 'public-read'
      ));
    } catch (S3Exception $e) {
      echo $e->getMessage() . "\n";
    }
  }

  public function setFiltertype($filterName, $filterArg) {
    if (strpos($filterName, 'bright') !== false) {
      $this->setFilterNum(IMG_FILTER_BRIGHTNESS, $filterArg);
    }else if (strpos($filterName, 'blur') !== false) {
      $this->setFilterNum(IMG_FILTER_GAUSSIAN_BLUR, $filterArg);
    }else if (strpos($filterName, 'removal') !== false) {
      $this->setFilterNum(IMG_FILTER_MEAN_REMOVAL, $filterArg);
    }else if (strpos($filterName, 'pixelate') !== false) {
      $this->setFilterNum(IMG_FILTER_PIXELATE, $filterArg);
    }else if (strpos($filterName, 'mono') !== false) {
      $this->setFilterNum(IMG_FILTER_GRAYSCALE, $filterArg);
    }
  }

  public function getFiltertype() {
    $s3 = \Aws\S3\S3Client::factory();
    $bucket = getenv('S3_BUCKET')?: die('No "S3_BUCKET" config var in found in env!');

    try {
      $numResult = $s3->getObject(array(
        'Bucket' => $bucket,
        'Key'    => 'data/filter_num.txt'
      ));
      $argResult = $s3->getObject(array(
        'Bucket' => $bucket,
        'Key'    => 'data/filter_arg.txt'
      ));
      header("Content-Type: {$numResult['ContentType']}");
      return array((int)strval($numResult['Body']), (int)strval($argResult['Body']));
    } catch (S3Exception $e) {
      echo $e->getMessage() . "\n";
    }
  }

  public function edit($originImage) {
    $filterType = array(
      'num' => $this->getFiltertype()[0],
      'arg' => $this->getFiltertype()[1]
    );
    if($filterType['arg'] == -999){
      ob_start();
      imagefilter($originImage, $filterType['num']);
      imagejpeg($originImage);
      $editedImage = ob_get_contents();
      ob_end_clean();
      return $editedImage;
    }
    ob_start();
    imagefilter($originImage, $filterType['num'], $filterType['arg']);
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
