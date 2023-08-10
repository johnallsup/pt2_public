<?php
/*
 * Purple Tree 2
 * Copyright John Allsup 2023
 * You may redistribute this software under the terms of the GNU GPL v3.
 * See LICENSE.txt
 */


require_once("breadcrumbs.php");
if( ! isset($navbar_rendered) ) {
  $navbar_rendered = "";
}
if( ! isset($page_rendered) ) {
  $page_rendered = "no page";
}
if( ! isset($headerTitle) ) {
  $headerTitle = "Header Title";
}
require("TemplateView.php");