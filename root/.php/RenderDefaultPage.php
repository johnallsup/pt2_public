<?php
/*
 * Purple Tree 2
 * Copyright John Allsup 2023
 * You may redistribute this software under the terms of the GNU GPL v3.
 * See LICENSE.txt
 */

if( $action === "edit" ) {
  $page_source = "";
} else {
  $t = "Page **".$pagename."** in *".$subdir."* does not exist.";
  if( is_auth("edit") ) {
    $pagename_u = urlencode($pagename);
    $t .= " [Edit to create]($pagename_u?action=edit)[e].";
    $t .= " or go to e.g. [[$pagename_u/home]][h] and create a page to make a subdirectory.";
  }
  $filenames = $storage->getregex($subdir,$pagename,"i");
  if( count($filenames) > 0 ) {
    $q = "";
    foreach($filenames as $fn) {
      if( preg_match('/(^.*)\.ptmd$/',$fn,$m) ) {
        $q .= "* [[".$m[1]."]]\n";
      }
    }
    if( $q !== "" ) {
      $t .= "\n\nPerhaps you mean:\n$q\n";
    }
  }
  $page_source = "$t";
}
require("RenderPage.php");