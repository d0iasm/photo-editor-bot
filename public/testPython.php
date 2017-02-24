<?php
  echo('hoge');
  $fullPath ='/Users/DOIasami/.pyenv/shims/python ../src/python/testPython.py abcd 1234';
  system($fullPath, $outpara);
  echo '<PRE>';
  var_dump($fullPath);
  var_dump($outpara[0]);
  var_dump($outpara[1]);
  var_dump($outpara[2]);
  var_dump($outpara[3]);
  echo '<PRE>';

  // $args = array_map('escapeshellarg', ['hoge', 'fuga']);
  // $cmd = vsprintf('python ../src/python/testPython.py %s %s 2>&1', $args);
  // system($cmd, $output, $status);
  // $output = implode($output);
  // var_dump(compact('output', 'status'));

  // $fullPath ='python ../src/python/filter.py';
  // exec($fullPath, $outpara);
  //
  // echo('hoge')
