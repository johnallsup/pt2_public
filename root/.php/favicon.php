<?php
/*
 * Purple Tree 2
 * Copyright John Allsup 2023
 * You may redistribute this software under the terms of the GNU GPL v3.
 * See LICENSE.txt
 */

if( isset($wiki->config["favicon"]) ) {
  $favicon = $wiki->config["favicon"];
  if( $favicon !== "" ) {
    $favicon_href = null;
    if( $favicon[0] == "/" ) {
      $favicon_href = $favicon;
    } else {
      $favicons = $storage->find_leaf_to_root($subdir,$favicon);
      if( count($favicons) > 0 ) {
        $favicon_href = "/".$favicons[0];
      }
    }
    if( $favicon_href !== null ) {
      echo "<link rel='icon' type='image/x-icon' href='$favicon_href'>";
    }
  }
}