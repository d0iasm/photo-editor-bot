<?php
namespace LINE\LINEBot\PhotoEditor;

$filtertype = IMG_FILTER_GRAYSCALE;

class Editor
{
  // private static $filtertype = IMG_FILTER_GRAYSCALE;

  private function __construct() {}

  public function setFiltertype($filterName) {
    if (strpos($filterName, 'gray') !== false) {
      $GLOBALS['filtertype'] = IMG_FILTER_GRAYSCALE;
    }else if (strpos($filterName, 'nega') !== false) {
      $GLOBALS['filtertype'] = IMG_FILTER_NEGATE;
    }else if (strpos($filterName, 'edge') !== false) {
      $GLOBALS['filtertype'] = IMG_FILTER_EDGEDETECT;
    }else if (strpos($filterName, 'removal') !== false) {
      $GLOBALS['filtertype'] = IMG_FILTER_MEAN_REMOVAL;
    }else if (strpos($filterName, 'emboss') !== false) {
      $GLOBALS['filtertype'] = IMG_FILTER_EMBOSS;
    }
    $GLOBALS['filtertype'] = IMG_FILTER_EMBOSS;
    return $GLOBALS['filtertype'];
  }

  public function getFiltertype() {
    return $GLOBALS['filtertype'];
  }

  public function edit($originImage) {
    ob_start();
    imagefilter($originImage, $GLOBALS['filtertype']);
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
