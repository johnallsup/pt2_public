<?php
/*
 * Purple Tree 2
 * Copyright John Allsup 2023
 * You may redistribute this software under the terms of the GNU GPL v3.
 * See LICENSE.txt
 */

require_once("docroot.php");
require_once("common.php");

if( !is_auth("read") ) {
  include("AccessDenied.php");
  exit();
}

$url = $_SERVER['REQUEST_URI'];
$url = preg_replace("@^/+@","",$url);
$staticroot = $docroot."/../static";
$fpath = $staticroot."/".$url;
if( is_file($fpath) ) {
  $mime = get_mime_type_for_fpath($fpath);
  http_response_code(200);
  header("Content-type: $mime");
  readfile($fpath);
} else {
  http_response_code(404);
  echo "File not found.";
  exit();
}
?>