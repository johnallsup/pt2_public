<?php
/*
 * Purple Tree 2
 * Copyright John Allsup 2023
 * You may redistribute this software under the terms of the GNU GPL v3.
 * See LICENSE.txt
 */

function breadcrumbs($path) {
  $wikiname = SITE_SHORT_TITLE;
  $xs = explode("/",trim($path,"/"));
  $ys = array();
  $z = ""; # accumulate path
  array_push($ys,"<a href='/'>$wikiname</a>");
  foreach($xs as $x) {
    $z .= "/$x";
    array_push($ys,"<a href='$z'>$x</a>");
  }
  return implode("/",$ys);
}