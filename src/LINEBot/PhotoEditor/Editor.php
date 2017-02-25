<?php
namespace LINE\LINEBot\PhotoEditor;

class Editor
{
  private $filtertype = IMG_FILTER_GRAYSCALE;

  public function setFiltertype($filterName) {
    if ($filterName == 'mono') {
      $this->filtertype = IMG_FILTER_GRAYSCALE;
    }else if ($filterName == 'nega') {
      $this->filtertype = IMG_FILTER_NEGATE;
    }else if ($filterName == 'edge') {
      $this->filtertype = IMG_FILTER_EDGEDETECT;
    }else if ($filterName == 'removal') {
      $this->filtertype = IMG_FILTER_MEAN_REMOVAL;
    }else if ($filterName == 'emboss') {
      $this->filtertype = IMG_FILTER_EMBOSS;
    }
  }

  public function getFiltertype() {
    return $this->filtertype;
  }

  public function edit($originImage) {
    ob_start();
    imagefilter($originImage, $filtertype);
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
}
