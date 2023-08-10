<?php
/*
 * Purple Tree 2
 * Copyright John Allsup 2023
 * You may redistribute this software under the terms of the GNU GPL v3.
 * See LICENSE.txt
 */

// COMMON
require_once("page_source.php");

$page_source_parsed = new PageSource($page_source);
$page_source = $page_source_parsed->src;
if( !is_null($navbar_source) ) {
  $navbar_source_parsed = new PageSource($navbar_source);
  $navbar_source = $navbar_source_parsed->src;
} else {
  $navbar_source_parsed = null;
}
$meta = $page_source_parsed->meta;
$options = $page_source_parsed->options;
$tags = $page_source_parsed->tags;

if( isset($meta["title"]) ) {
  $headerTitle = $meta["title"];
}

// PAGE SPECIFIC
$ptmd = new PTMD($wiki);
$page_rendered = $ptmd->render($page_source,$options);
$uses = $ptmd->uses;
if( isset($uses["abc"]) ) {
  $scripts->add("<script src='/js/abcjs-basic-min.js'></script>");
  $scripts->add("<script src='/js/abc-auto.js'></script>");
}
if( isset($uses["math"]) ) {
  $scripts->add("<script>
MathJax = {
  tex: {
    inlineMath: [['\\\\(', '\\\\)']],
    displayMath: [['\\\\[', '\\\\]']]
  },
  svg: {
    fontCache: 'global'
  }
}
</script>");
  $scripts->add("<script type='text/javascript' id='MathJax-script' async
  src='https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-svg.js'></script>");
}
if( !is_null($navbar_source) ) {
  $navbar_rendered = "<nav>
  ".$ptmd->render($navbar_source,$options)."
</nav>";
} else {
  $navbar_rendered = "";
}
require("TemplateView.php");