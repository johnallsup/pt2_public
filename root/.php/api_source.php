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
$source = $storage->has($path) ? $storage->get($path) : null;
$response_data = [ "path" => $path, "source" => $source, "debug_received" => $postdata ];
serve_json($response_data,200);