<?php
/*
 * Purple Tree 2
 * Copyright John Allsup 2023
 * You may redistribute this software under the terms of the GNU GPL v3.
 * See LICENSE.txt
 */

require_once("ptmd.php");
require_once("breadcrumbs.php");
$scripts->addscr("window.pageName = '".$wiki->pagename."'\nwindow.pagePath = '".$wiki->url."'");

$a = basename($wiki->path);
$b = dirname($wiki->path);
$n = $wiki->pagename;
if( $n === "home" ) {
  if( $b !== "." ) {
    $n .= " <span class='homesuffix'>$b</span>";
  }
}
$headerTitle = $n;

// RENDER
if( $wiki->action === "view" ) {
  require("RenderView.php");
} else if( $wiki->action === "edit" ) {
  require("RenderEdit.php" );
} else {
  echo "Invalid action $wiki->action\n";
  exit();
}