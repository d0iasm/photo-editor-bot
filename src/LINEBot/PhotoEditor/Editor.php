<?php
namespace LINE\LINEBot\PhotoEditor;

class Editor
{
  private static $filtertype = IMG_FILTER_GRAYSCALE;

  private function __construct() {}

  public function setFiltertype($filterName) {
    if (strpos($filterName, 'gray') !== false) {
      self::$filtertype = IMG_FILTER_GRAYSCALE;
    }else if (strpos($filterName, 'nega') !== false) {
      self::$filtertype = IMG_FILTER_NEGATE;
    }else if (strpos($filterName, 'edge') !== false) {
      self::$filtertype = IMG_FILTER_EDGEDETECT;
    }else if (strpos($filterName, 'removal') !== false) {
      self::$filtertype = IMG_FILTER_MEAN_REMOVAL;
    }else if (strpos($filterName, 'emboss') !== false) {
      self::$filtertype = IMG_FILTER_EMBOSS;
    }
    self::$filtertype = IMG_FILTER_EMBOSS;
    return self::$filtertype;
  }

  public function getFiltertype() {
    return self::$filtertype;
  }

  public function edit($originImage) {
    ob_start();
    imagefilter($originImage, self::$filtertype);
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
