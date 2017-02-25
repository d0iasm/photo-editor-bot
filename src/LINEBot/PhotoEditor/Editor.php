<?php
namespace LINE\LINEBot\PhotoEditor;

class Editor
{
  private $filtertype = IMG_FILTER_GRAYSCALE;
  // public $num = 0;

  private function __construct() {}

  // public function addNum(){
  //   $editor->num += 3;
  // }
  //
  // public function testMethod()
  // {
  //   $editor = self::getInstance();
  //   $editor->addNum();
  //   return $editor->num;
  // }

  public static function setFiltertype($filterName) {
    $editor = self::getInstance();
    if (strpos($filterName, 'gray') !== false) {
      $editor->filtertype = IMG_FILTER_GRAYSCALE;
    }else if (strpos($filterName, 'nega') !== false) {
      $editor->filtertype = IMG_FILTER_NEGATE;
    }else if (strpos($filterName, 'edge') !== false) {
      $editor->filtertype = IMG_FILTER_EDGEDETECT;
    }else if (strpos($filterName, 'removal') !== false) {
      $editor->filtertype = IMG_FILTER_MEAN_REMOVAL;
    }else if (strpos($filterName, 'emboss') !== false) {
      $editor->filtertype = IMG_FILTER_EMBOSS;
    }
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
    static $instance;
    if ($instance === null) {
      $instance = new self();
    }
    return $instance;
  }
}
