<?php
/*
 * Purple Tree 2
 * Copyright John Allsup 2023
 * You may redistribute this software under the terms of the GNU GPL v3.
 * See LICENSE.txt
 */

require_once("write_log.php");
class Wiki {
  # Holds context data that we can pass to e.g. PTMD
  var $storage, $path, $subdir, $url, $action, $config;
  function log($message) {
    return write_log($message);
  }
  function valid_page_path($path) {
    if( preg_match('@^[st](?:$|/)@',$path) ) {
      return false; # s/ t/ reserved for tag/search
    }
    if( preg_match('@(?:^|/)home/@',$path) ) {
      # home is not a valid component of a directory name
      return false;
    }
    if( preg_match('/^([a-zA-Z0-9_+@=-]+\/+)*[a-zA-Z0-9_+%@=-]+$/',$path) ) {
      return true;
    }
    return false;
  }
  function valid_file_path($path) {
    if( preg_match('@^[st](?:$|/)@',$path) ) {
      return false; # s/ t/ reserved for tag/search
    }
    if( preg_match('/^([a-zA-Z0-9_+@=-]+\/+)*[a-zA-Z0-9_+%@=-]+\.[a-zA-Z0-9_+%@=-]+$/',$path) ) {
      return true;
    }
    return false;
  }
  function redirect($newpath) {
    header('Location: '.$newpath, true, 303);
    exit();
  }
}