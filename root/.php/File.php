<?php
/*
 * Purple Tree 2
 * Copyright John Allsup 2023
 * You may redistribute this software under the terms of the GNU GPL v3.
 * See LICENSE.txt
 */

require_once("docroot.php");
require_once("common.php");

$url = $_SERVER['REQUEST_URI'];
$url = preg_replace("@^/+@","",$url);
$storage = new VersionedStorage($docroot."/../files",$docroot."/../version");
if( !is_auth("read") ) {
  include("AccessDenied.php");
  exit();
}
if( $storage->has($url) ) {
  $mime = $storage->get_mime_type($url);
  $fpath = $storage->fpath($url);
  http_response_code(200);
  header("Content-type: $mime");
  readfile($fpath);
}
?>