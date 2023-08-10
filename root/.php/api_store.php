<?php
/*
 * Purple Tree 2
 * Copyright John Allsup 2023
 * You may redistribute this software under the terms of the GNU GPL v3.
 * See LICENSE.txt
 */

require_once("utils.php");
require_once("mtimes.php");
if( !is_auth("edit") ) {
  serve_error_json("accessdenied","Access denied trying to store",401);
}
if( !isset($postdata["path"]) ) {
  serve_error_json("invalidstore","No path provided for store",400,[ "postdata" => $postdata]);
}
if( !isset($postdata["source"]) ) {
  serve_error_json("invalidstore","No source provided for store",400,[ "postdata" => $postdata]);
}
$path = $postdata["path"].".".PAGE_EXT;
$source = $postdata["source"];
$storage = $wiki->storage;
try {
  $source = trim($source);
  if( $source === "" ) {
    $result = $storage->del($path);
    $when = time();
    [ $mtime_fmt_long, $mtime_fmt_short, $mtime_fmt_short_ago ] = fmt_time($when);
    if( $result ) {
      serve_json([
        "status" => "success", 
        "message" => "Deleted $path successfully",
        "mtime" => $when,
        "mtime_fmt_short" => $mtime_fmt_short,
        "mtime_fmt_long" => $mtime_fmt_long,
        "mtime_fmt_short_ago" => $mtime_fmt_short_ago
      ],
        200);
    } else {
      serve_json([
        "status" => "error", 
        "message" => "Failed to delete $path",
        "mtime" => $when,
        "mtime_fmt_short" => $mtime_fmt_short,
        "mtime_fmt_long" => $mtime_fmt_long,
        "mtime_fmt_short_ago" => $mtime_fmt_short_ago
      ],
        200);
    }
  } else {
    $when = $storage->store($path,$source);
    [ $mtime_fmt_long, $mtime_fmt_short, $mtime_fmt_short_ago ] = fmt_time($when);
    # write very recent
    $recent_writes_entry = "$when:$path";
    append_to_data("recent_writes.log",$recent_writes_entry."\n");
    serve_json([
      "status" => "success", 
      "message" => "Stored $path successfully",
      "mtime" => $when,
      "mtime_fmt_short" => $mtime_fmt_short,
      "mtime_fmt_long" => $mtime_fmt_long,
      "mtime_fmt_short_ago" => $mtime_fmt_short_ago
    ],
      200);
  }
} catch(Exception $e) {
  serve_error_json("storeerror","Failed to store",500,["exception" => $e->getMessage()]);
}