<?php
/*
 * Purple Tree 2
 * Copyright John Allsup 2023
 * You may redistribute this software under the terms of the GNU GPL v3.
 * See LICENSE.txt
 */


require("PageLike.php");
$headerTitle = "Word Matching Regex";

$url = strtolower($url);
$search_patterns = explode("/",$url);
$base_url_components = [];
while( count($search_patterns) > 0 && $search_patterns[0][0] !== "." ) {
  array_push($base_url_components,array_shift($search_patterns));
}
$base_url = implode("/",$base_url_components);
if( count($search_patterns) === 0 ) {
  echo "No special 4298";
  exit();
}
$special = array_shift($search_patterns);

if( count($search_patterns) == 0 ) {
  $search_patterns = ["."];
}

$by_word = from_data_json("by_word_ic");
$words = array_keys($by_word);
$matches = [];
foreach($words as $word) {
  if( ! preg_match('@^[a-zA-Z]@',$word) ) {
    continue;
  }
  foreach($search_patterns as $search_pattern) {
    if( preg_match("/$search_pattern/",$word) ) {
      array_push($matches,$word);
      break;
    }
  }
}

$html = "<div class='search-results'>\n";
$t = "<ul class='search-result search-terms'>\n";
foreach($search_patterns as $pattern) {
  $t .= "<li class='search-term'>$pattern</li>\n";
}
$t .= "</ul>\n";
$html .= $t;

if( count($matches) == 0 ) {
  $html .= "<p class='search-result no-match no-matching-pages'>No matches.</p>\n";
} else {
  sort($matches);
  $html .= "<ol>\n";
  foreach($matches as $word) {
    $href = "/.w/$word";
    if( $base_url !== "" ) {
      $href = "/".$base_url.$href;
    }
    $t = "<a class='pagelink' href='$href'>$word</a>";
    $html .= "  <li>$t</li>\n";
  }
  $html .= "</ol>\n";
}

$page_rendered = $html;

require("RenderPageLike.php");