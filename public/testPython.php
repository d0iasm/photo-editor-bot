<?php
  $fullPath ='python ../src/python/testPython.py abcd 1234';
  exec($fullPath, $outpara);
  echo '<PRE>';
  var_dump($fullPath);
  var_dump($outpara[0]);
  var_dump($outpara[1]);
  echo '<PRE>';
