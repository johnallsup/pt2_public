<?php
/*
 * Purple Tree 2
 * Copyright John Allsup 2023
 * You may redistribute this software under the terms of the GNU GPL v3.
 * See LICENSE.txt
 */

require("PageLike.php");

// if the url names a directory d, redirect to d/home
if( $storage->isdir($url) ) {
  $url = trim($url,"/");
  $wiki->redirect("/$url/home");
  exit();
}
#if( !preg_match("@^([a-zA-Z0-9_+\\@=-]+/+)*([a-zA-Z0-9_+%\\@=-]+)$@",$url,$m) ) {
#  echo "should not get here (check .htaccess) $url\n";
#  exit();
#}
$path = $url.".".PAGE_EXT;
$navbar_path = $subdir."/.navbar.".PAGE_EXT;

# Page specific -- PageLike's should copy and modify this bit
$wiki->path = $path;
$wiki->navbar_path = $navbar_path;

http_response_code(200);
header("Content-type: text/html");
if( $storage->has($navbar_path) ) {
  $navbar_source = $storage->get($navbar_path);
} 
function invalid_action($action,$msg = "") {
  http_response_code(400);
  $page_source = "Invalid action: $action";
  if( $msg !== "" ) $page_source .= " ".$msg;
  $wiki->action = "view";
  require("RenderPage.php");
  exit();
}
if( isset($_GET['action']) ) {
  $action = $_GET['action'];
  if( ! preg_match("@^view|edit|versions$@",$action) ) {
    echo "fail $action\n";
    invalid_action($action,"1");
  }
} else {
  $action = "view";
}
if( $wiki->action === "versions" ) {
  http_response_code(200);
  require("RenderVersions.php");
  exit();
}
if( isset($_GET['version']) ) {
  if( $action !== "view" && $action !== "edit" ) {
    invalid_action($action,"for version");
    exit();
  }
  $when = $_GET['version'];
  if( ! $storage->has_version($path,$when) ) {
    $page_source = "Version $when for page $path does not exist.";
  } else { 
    $page_source = $storage->get_version($path,$when);
    $wiki->pagemtime = $when;
  }
  require("RenderPage.php");
  exit();
}
if( ! $storage->has($path) ) {
  http_response_code(200);
  if( $pagename === "home" ) {
    require("RenderDefaultHomePage.php");
  } else {
    require("RenderDefaultPage.php");
  }
  exit();
}
$page_source = $storage->get($path);
$wiki->pagemtime = $storage->getmtime($path);
require("RenderPage.php");

?>