<?php
/*
 * Purple Tree 2
 * Copyright John Allsup 2023
 * You may redistribute this software under the terms of the GNU GPL v3.
 * See LICENSE.txt
 */


require("PageLike.php");
require_once("mtimes.php");

$html = "<div class='image-directory'>\n";
$dn = $wiki->subdir;
if( $dn === "" ) {
  $ddn = "/";
} else {
  $ddn = $dn;
}
$headerTitle = "Images in $ddn";
$rows = [];

[ $dirs, $pages, $files ] = get_dir_contents($storage,$subdir);
$files = array_map(function($x) {
   return "<li class='img-dir-entry'><a href='$x'><img src='$x'/><span class='img-filename'>$x</span></a>"; },$files);

if( count($files) > 0 ) {
  $t = "<ol class='directory-list dir-images'>".implode(" ",$files)."</ol>";
} else {
  $t = "<p>Directory ".$ddn." is contains no images.</p>";
}

$page_source = "<Image Directory>";
$page_rendered = $t;

require("RenderPageLike.php");
