<?php
namespace LINE\LINEBot\PhotoEditor;

class Editor
{
  private static $filtertype = IMG_FILTER_GRAYSCALE;

  private function __construct() {}

  public function setFiltertype($filterName) {
    static $filtertype = IMG_FILTER_GRAYSCALE;
    if (strpos($filterName, 'gray') !== false) {
      $filtertype = IMG_FILTER_GRAYSCALE;
    }else if (strpos($filterName, 'nega') !== false) {
      $filtertype = IMG_FILTER_NEGATE;
    }else if (strpos($filterName, 'edge') !== false) {
      $filtertype = IMG_FILTER_EDGEDETECT;
    }else if (strpos($filterName, 'removal') !== false) {
      $filtertype = IMG_FILTER_MEAN_REMOVAL;
    }else if (strpos($filterName, 'emboss') !== false) {
      $filtertype = IMG_FILTER_EMBOSS;
    }
    $filtertype = IMG_FILTER_EMBOSS;
    return $filtertype;
  }

  public function getFiltertype() {
    return $filtertype;
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
    if (!isset($instance)) {
      $instance = new self();
    }
    return $instance;
  }
}
