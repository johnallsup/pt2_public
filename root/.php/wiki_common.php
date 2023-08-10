<?php
/*
 * Purple Tree 2
 * Copyright John Allsup 2023
 * You may redistribute this software under the terms of the GNU GPL v3.
 * See LICENSE.txt
 */


$storage = new VersionedStorage($docroot."/".FILES_ROOT,$docroot."/".VERSIONS_ROOT);

if( isset($_GET['action']) ) {
  $action = $_GET['action'];
} else {
  $action = "view";
}

$wiki = new Wiki();
$url = $_SERVER['REQUEST_URI'];
if( preg_match('@//+@',$url) ) {
  $newurl = preg_replace('@//+@','/',$url);
  $wiki->redirect($newurl); 
}
$url = explode("?",$url,2)[0];
$url = preg_replace("@^/+@","",$url);
if( !preg_match("@^(.*/)?([^/]*)$@",$url,$m) ) {
  http_response_code(400);
  echo "invalid path 1453: $url";
  exit();
}

[ $x, $subdir, $pagename ] = $m;
$subdir = rtrim($subdir,"/");
$wiki->storage = $storage;
$wiki->action = $action;
$wiki->url = $url;
$wiki->pagename = $pagename;
$wiki->subdir = $subdir;
$wiki->config = get_config($wiki);
$wiki->pagemtime = 0;
$wiki->path = null;
$wiki->navbar_path = null;
