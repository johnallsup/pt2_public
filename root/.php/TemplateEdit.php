<?php
/*
 * Purple Tree 2
 * Copyright John Allsup 2023
 * You may redistribute this software under the terms of the GNU GPL v3.
 * See LICENSE.txt
 */


require_once("mtimes.php");
fmt_pagemtime($wiki->pagemtime);

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
$more_options = null;
require("TemplateEdit_Header.php");
?>
</header>
<section class="main">
<textarea name='source' class="editor" autofocus><?php echo htmlspecialchars($page_source); ?></textarea><br/>
</section>
</div>
</body>
</html>