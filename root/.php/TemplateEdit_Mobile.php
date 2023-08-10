<?php
/*
 * Purple Tree 2
 * Copyright John Allsup 2023
 * You may redistribute this software under the terms of the GNU GPL v3.
 * See LICENSE.txt
 */


require_once("mtimes.php");
fmt_pagemtime($wiki->pagemtime);

$scripts->add("<script src='/js/wiki_edit.js'></script>");

// TODO: REFACTOR ONCE DONE, MOVE COMMON STUFF OUTSIDE THE SPECIFIC TEMPLATES
?><!DOCTYPE html>
<html>
<head>
  <meta charset='utf8'/>
  <title><?php echo "$pagename : /$subdir : ".SITE_SHORT_TITLE; ?></title>

<?php
require("favicon.php");
?>
<?php
require("localconfig.php");
?>
<?php
echo $htmlmeta->join("\n")."\n\n";
echo $scripts->join("\n")."\n\n";
echo $styles->join("\n")."\n\n";
?>
</head>
<body>
<div class="container">
<header>
<?php
$more_options = "<span class='action mo-leftarrow block'>&#x2190;</span>
<span class='action mo-rightarrow block'>&#x2192;</span>
<span class='action mo-prevheader block'>#-</span>
<span class='action mo-nextheader block'>#+</span>
<span class='action mo-prevline block'>&#x2191;</span>
<span class='action mo-nextline block'>&#x2193;</span>";
require("TemplateEdit_Header_Mobile.php");
?>
</header>
<section class="main">
<textarea name='source' class="editor" cols='80' rows='25' autofocus><?php echo htmlspecialchars($page_source); ?></textarea><br/>
</section>
</div>
</body>
</html>