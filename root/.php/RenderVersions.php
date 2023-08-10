<?php
/*
 * Purple Tree 2
 * Copyright John Allsup 2023
 * You may redistribute this software under the terms of the GNU GPL v3.
 * See LICENSE.txt
 */


require_once("mtimes.php");

$versions = $storage->get_version_times($path);
if( count($versions) == 0 ) {
  http_response_code(404);
  $page_source = "There are no versions for $path";
} else {
  $t = "## Versions for $path\n";
  sort($versions,SORT_NUMERIC); 
  $versions = array_reverse($versions);
  foreach($versions as $version) {
    [ $mtime_fmt_long, $mtime_fmt_short ] = fmt_time($version);
    $t .= "* [$mtime_fmt_long]($wiki->pagename?version=$version)\n";
  }
  $page_source = $t;
}

$wiki->action = "view";
require("RenderPage.php");