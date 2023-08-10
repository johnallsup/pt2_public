<?php
/*
 * Purple Tree 2
 * Copyright John Allsup 2023
 * You may redistribute this software under the terms of the GNU GPL v3.
 * See LICENSE.txt
 */

require_once("common.php");

$url = substr($_SERVER["REQUEST_URI"],1);
$url0 = $url;
$url = preg_replace('@//+@','/',$url);
$url = ltrim($url,"/");
if( $url !== $url0 ) {
  header("Location: /$url", true, 303);
  exit();
}

$us = explode("/",$url);
$si = -1;
foreach($us as $i => $v) {
  if( $v[0] === "." ) {
    $si = $i;
    break;
  }
}
if( $si >= 0 ) {
  $ul = array_slice($us,0,$si);
  $ur = array_slice($us,$si+1);
  $ux = $us[$si];
  $ux = explode("?",$ux)[0];
  $l = implode("/",$ul);
  $r = implode("/",$ur);
  #echo "l='$l' x='$ux' r='$r'\n";
  switch($ux) {
  case ".w":
    #echo "Word search\n";
    $case_sensitive = false;
    require("special_WordSearch.php");
    return;
  case ".wc":
  case ".c":
    #echo "Case sensitive word search\n";
    $case_sensitive = true;
    require("special_WordSearch.php");
    return;
  case ".wr":
    #echo "Words matching regex (site-wide)\n";
    require("special_WordsRegex.php");
    return;
  case ".r":
  case ".recent":
    $recent = "pages";
    #echo "Recent pages\n";
    require("special_Recent.php");
    return;
  case ".rf":
  case ".recf":
  case ".recentf":
  case ".recentfiles":
    $recent = "files";
    echo "Recent pages\n";
    require("special_Recent.php");
    return;
  case ".t":
  case ".t":
    #echo "Tag search\n";
    require("special_TagSearch.php");
    return;
  case ".d":
  case ".dir":
    echo "Dir\n";
    $dir_type = "all";
    require("special_Dir.php");
    return;
  case ".dd":
    $dir_type = "dirs";
    require("special_Dir.php");
    return;
  case ".df":
  case ".f":
    echo "Dir (files)\n";
    $dir_type = "files";
    require("special_Dir.php");
    return;
  case ".dp":
  case ".p":
    echo "Dir (pages)\n";
    $dir_type = "pages";
    require("special_Dir.php");
    return;
  case ".di":
  case ".i":
    require("special_ImgDir.php");
    return;
  case ".navbar":
    require("Page.php");
    return;
  case ".config":
    require("Page.php");
    return;
  case ".about":
    require("Page.php");
    return;
  default:
    echo "Unrecognised $ux\n";
    break;
  }
} else {
  echo "Invalid special URL\n";
}
?><h1>Special</h1>
