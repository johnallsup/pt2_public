<?php
/*
 * Purple Tree 2
 * Copyright John Allsup 2023
 * You may redistribute this software under the terms of the GNU GPL v3.
 * See LICENSE.txt
 */


require("PageLike.php");
require_once("ptmd.php");

$navbar_source = "";
$page_source = "# Search

To search, use a url of the form:
* `/.w/search/terms` for pages matching at least one term
* `/.w/.a/search/terms` for pages matching all of the terms
* `/.t/tag` for pages with matching tags
* `/.c/Search/TeRms` for case insensitive search (can use `.a`)
";

$ptmd = new PTMD($wiki);
$page_rendered = $ptmd->render($page_source,[]);

require("RenderPageLike.php");