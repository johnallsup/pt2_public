<?php
/*
 * Purple Tree 2
 * Copyright John Allsup 2023
 * You may redistribute this software under the terms of the GNU GPL v3.
 * See LICENSE.txt
 */

if( !is_auth("edit") ) {
  serve_error_json("accessdenied","Access denied trying to preview",401);
}
if( !isset($postdata["path"]) ) {
  serve_error_json("invalidstore","No path provided for preview",400);
}
if( !isset($postdata["source"]) ) {
  serve_error_json("invalidstore","No source provided for preview",400);
}

require_once("utils.php");
require_once("ptmd.php");
require_once("page_source.php");

$path = $postdata["path"];
$source = $postdata["source"];

$page_source_parsed = new PageSource($source);
$meta = $page_source_parsed->meta;
$options = $page_source_parsed->options;
$tags = $page_source_parsed->tags;

$ptmd = new PTMD($wiki);
$page_rendered = $ptmd->render($source,$options);
$uses = $ptmd->uses;

$response_data = [ 
  "path" => $path,
  "source" => $source,
  "rendered" => $page_rendered,
  "uses" => $uses,
  "debug_received" => $postdata 
];
serve_json($response_data,200);
