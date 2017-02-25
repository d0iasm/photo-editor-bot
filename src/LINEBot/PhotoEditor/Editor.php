<?php
namespace LINE\LINEBot\PhotoEditor;

class Editor
{
  private static $instance;

  private static $filtertype = IMG_FILTER_GRAYSCALE;

  public function setFiltertype($filterName) {
    if ($filterName == 'mono') {
      self::$filtertype = IMG_FILTER_GRAYSCALE;
    }else if ($filterName == 'nega') {
      self::$filtertype = IMG_FILTER_NEGATE;
    }else if ($filterName == 'edge') {
      self::$filtertype = IMG_FILTER_EDGEDETECT;
    }else if ($filterName == 'removal') {
      self::$filtertype = IMG_FILTER_MEAN_REMOVAL;
    }else if ($filterName == 'emboss') {
      self::$filtertype = IMG_FILTER_EMBOSS;
    }
  }

  public function getFiltertype() {
    return self::$filtertype;
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
    if (!isset(self::$instance)) {
      self::$instance = new Editor();
    }
    return self::$instance;
  }
}
