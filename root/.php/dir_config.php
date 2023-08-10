<?php
/*
 * Purple Tree 2
 * Copyright John Allsup 2023
 * You may redistribute this software under the terms of the GNU GPL v3.
 * See LICENSE.txt
 */


function get_config($wiki) {
  $storage = $wiki->storage;
  $subdir = $wiki->subdir;
  $configs = $storage->find_root_to_leaf($subdir,".config.".PAGE_EXT);
  $config_src = "";
  foreach($configs as $config) {
    $config_src .= $storage->get($config)."\n";
  }
  $lines = explode("\n",$config_src);
  $config = array();
  foreach($lines as $line) {
    if( preg_match("/^([a-zA-Z0-9_-]+)=(.*)$/",$line,$m) ) {
      $k = $m[1];
      $v = $m[2];
      $v = trim(explode(" #",$v)[0]); // remove comments
      $config[$k] = $v;
    }
  }
  return $config;
}
