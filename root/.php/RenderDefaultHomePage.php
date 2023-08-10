<?php
/*
 * Purple Tree 2
 * Copyright John Allsup 2023
 * You may redistribute this software under the terms of the GNU GPL v3.
 * See LICENSE.txt
 */

require("dir_maker.php");
function generate_dir_content($wiki,$dir) {
  $storage = $wiki->storage;

  $pages = array_map(function($x) { $a = basename($x,".".PAGE_EXT); return "[[$a]]";; },$dir->pages);
  $dirs = array_map(function($x) { return "[[$x]]"; },$dir->dirs);
  $files = array_map(function($x) { return "[[$x]]"; },$dir->files);
  $t = "";
  if( count($pages) > 0 ) {
    $t .= "## Pages\n";
    $t .= implode(" ",$pages);
    $t .= "\n\n";
  }
  if( count($dirs) > 0 ) {
    $t .= "## Subdirectories\n";
    $t .= implode(" ",$dirs);
    $t .= "\n\n";
  }
  if( count($files) > 0 ) {
    $t .= "## Files\n";
    $t .= implode(" ",$files);
    $t .= "\n\n";
  }
  return $t;
}
if( $action === "edit" ) {
  $page_source = "";
} else {
  $about_path = ltrim($subdir."/.about.".PAGE_EXT,"/");
  $page_source = "";
  if( $storage->has($about_path) ) {
    $page_source .= "\n\n# About $subdir\n\n".$storage->get($about_path);
  } else {
    $sd = $subdir === "" ? "Wiki ".SITE_TITLE : $subdir;
    $page_source = "Default **HOME page** for *$sd*.";
  }
  $dir_maker = new DirMaker($wiki);
  $dir_maker->get_dir_contents();
  $dir = generate_dir_content($wiki,$dir_maker);
  if( $dir !== "" ) $page_source .= "\n\n# Directory\n\n$dir";
}
require("RenderPage.php");