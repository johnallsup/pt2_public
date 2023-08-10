<?php
/*
 * Purple Tree 2
 * Copyright John Allsup 2023
 * You may redistribute this software under the terms of the GNU GPL v3.
 * See LICENSE.txt
 */

if( ! isset($postdata["path"]) ) {
  serve_error_json("nopath","No path specified",400);
}
$path = $postdata["path"].".".PAGE_EXT;
$storage = $wiki->storage;
// Have a separate action=versions to get versions, since this only
// works with pages.
if( $storage->has_versions($path) ) {
  $versions = $storage->get_version_times($path);
} else {
  $versions = [];
}
$response_data = [ "path" => $path, "versions" => $versions, "debug_received" => $postdata ];
serve_json($response_data,200);