<?php
/*
 * Purple Tree 2
 * Copyright John Allsup 2023
 * You may redistribute this software under the terms of the GNU GPL v3.
 * See LICENSE.txt
 */


require("PageLike.php");
$headerTitle = "Tag Search";

$search_words = $ur;
$by_word = from_data_json("by_tag");
if( count($search_words) == 0 ) {
  require("WordSearchPage.php");
  return;
}
$matches = [];
$nomatches = [];
$words = [];
foreach($search_words as $word) {
  if( array_key_exists($word,$by_word) ) {
    $words[$word] = true;
    foreach($by_word[$word] as $i => $page) {
      $matches[$page] = true;
    }
  } else {
    $nomatches[$word] = true;
  }
}

$html = "<div class='search-results'>\n";
foreach($nomatches as $word => $val) {
  $html .= "<p class='search-result no-match not-in-index'>Tag '$word' not in the index.</p>\n";
}
$t = "<ul class='search-result search-terms'>\n";
foreach($words as $word => $val) {
  $t .= "<li class='search-term'>$word</li>\n";
}
$t .= "</ul>\n";
$html .= $t;

$matches = array_keys($matches);
$matches = array_filter($matches, function($x) use($l) {
  if( strlen($l) >= strlen($x) ) { return false; }
  if( substr($x,0,strlen($l)) !== $l ) { return false; }
  return true;
});
if( count($matches) == 0 ) {
  $html .= "<p class='search-result no-match no-matching-pages'>No matches.</p>\n";
} else {
  sort($matches);
  $html .= "<ol>\n";
  foreach($matches as $page) {
    $page = preg_replace("@\\.".PAGE_EXT."$@","",$page);
    $cs = explode("/",$page);
    $pn = array_pop($cs);
    $dn = implode("/",$cs);
    $t = "<a class='pagelink' href='/$page'>$pn</a> in ";
    if( $dn == "" ) {
      $t .= "<a class='dirlink' href='/'>root</a>";
    } else {
      $t .= "<a class='dirlink' href='/$dn/home'>$dn</a>";
    }
    $html .= "  <li>$t</li>\n";
  }
  $html .= "</ol>\n";
}

$page_rendered = $html;

require("RenderPageLike.php");