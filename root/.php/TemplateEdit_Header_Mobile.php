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
<a href="<?php echo $wiki->pagename;?>?action=edit" target="_blank" class="action duplicate-edit block">E</a>
<a href="<?php echo $wiki->pagename.'?action=versions';?>" class="action versions block">&#9419;</a>
<a href="<?php echo $wiki->pagename;?>" class="action abort block">X</a>
<span class="action show-preview block">P</span>
<span class="action more-options block">M</span>
<span class="action show-goto-box block">G</span>
<spspann class="spacer block"></span>
<span class="action save block">&#128190;</span>
</section>
<section class="title">
<h1><?php echo $headerTitle; ?></h1>
<div class="info spreadwide">
<span class="breadcrumbs"><?php
echo breadcrumbs($wiki->subdir);
?></span>
  
<?php
if( isset($_GET["version"]) ) {
  ?><span class="mtime" version="<?php echo $_GET["version"]; ?>">
    <span class="version-indicator">version:</span>
    <span class="time"><?php echo $mtime_fmt_short; ?></span>
    </span><?php
} else {
  ?><span class="mtime">
    <span class="time"><?php echo $mtime_fmt_short; ?></span>
    </span><?php
}
?>
</div>
</section>
<section subpage="hamburger">
<div class="text-size-options spread">
<button class='action editor-normal-font'>Normal</button>
<button class='action editor-large-font'>Large</button>
<button class='action editor-huge-font'>Huge</button>
</div>
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
<?php
if( $more_options ) {
  ?><section subpage='more-options'>
    <div><?php echo $more_options;?></div>
    </section><?php
}
?>

