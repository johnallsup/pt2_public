<?php
/*
 * Purple Tree 2
 * Copyright John Allsup 2023
 * You may redistribute this software under the terms of the GNU GPL v3.
 * See LICENSE.txt
 */

if( isset($wiki->config["css"]) ) {
  $config_css = $wiki->config["css"];
  $csss = explode(",",$config_css);
  foreach( $csss as $css ) {
    $css = trim($css);
    $css_paths = $storage->find_leaf_to_root($subdir,$css);
    if( count($css_paths) > 0 ) {
      $css_path = $css_paths[0];
      $styles->addsts("/".$css_path);
    }
  }
}