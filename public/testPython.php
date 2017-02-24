<?php
  echo('hoge');
  $fullPath ='python ../src/python/testPython.py abcd 1234';
  exec($fullPath, $outpara);
  echo '<PRE>';
  var_dump($fullPath);
  var_dump($outpara[0]);
  var_dump($outpara[1]);
  var_dump($outpara[2]);
  var_dump($outpara[3]);
  echo '<PRE>';

  $args = array_map('escapeshellarg', ['hoge', 'fuga']);
  $cmd = vsprintf('python ../src/python/testPython.py %s %s 2>&1', $args);
  exec($cmd, $output, $status);
  $output = implode($output);
  var_dump(compact('output', 'status'));

  // $fullPath ='python ../src/python/filter.py';
  // exec($fullPath, $outpara);
  //
  // echo('hoge')
