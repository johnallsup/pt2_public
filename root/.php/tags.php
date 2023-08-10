<?php
/*
 * Purple Tree 2
 * Copyright John Allsup 2023
 * You may redistribute this software under the terms of the GNU GPL v3.
 * See LICENSE.txt
 */

function make_tag($x) {
  $x = ltrim($x,"#");
  return "<span class='hashtag'><a href='/.t/$x'>".$x."</a></span>"; 
}
if( isset($meta["tags"]) ) {
  $tags = $meta["tags"];
  $tags = preg_split("/\s+/",trim($tags));
  $tags = array_map("make_tag",$tags);
  $tags = implode(" ",$tags);
  echo "<span class='hashtags'>$tags</span>";
}