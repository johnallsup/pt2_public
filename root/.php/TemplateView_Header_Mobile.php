<?php
/*
 * Purple Tree 2
 * Copyright John Allsup 2023
 * You may redistribute this software under the terms of the GNU GPL v3.
 * See LICENSE.txt
 */
?><section class="topbar">
<span class="action hamburger block">&#9776;</span>
<a href="<?php echo $wiki->pagename;?>" target="_blank" class="action duplicate block">&CirclePlus;</a>
<a href="<?php echo $wiki->pagename.'?action=versions';?>" class="action versions block">&#9419;</a>
<span class="action show-goto-box block">G</span>
<span class="spacer block"></span>
<?php
if( $can_edit ) {
  ?><a class="edit block" href="<?php echo pagename_with_action("edit"); ?>">&#x1F58A;</a><?php
}
?>
</section>
<section class="title">
<?php
require("Template_PageTitle.php");
?>
<div class="info spreadwide">
<span class="breadcrumbs"><?php
echo breadcrumbs($wiki->subdir);
?></span>
<?php
require('tags.php');
?>
  <span class="mtime">
<?php echo $mtime_fmt_short; ?></span>
</div>
</section>
<section subpage="hamburger" >
<div class="info other-info spreadwide">
<span class="file-size"><?php
$src = $page_source;
$nchars = strlen($src);
$words = preg_split("@\s+@s",$src);
$nwords = count($words);
$lines = explode("\n",$src);
$nlines = count($lines);
echo "$nlines lines, $nwords words, $nchars chars";
?></span>
<span class="spacer"></span><span class="mtime">
<?php echo $mtime_fmt_long; ?></span>
</div>
</section>
