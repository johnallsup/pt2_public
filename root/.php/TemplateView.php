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
/*
if( isset($wiki->config["css"]) ) {
  $config_css = $wiki->config["css"];
  $csss = explode(",",$config_css);
  foreach( $csss as $css ) {
    $css = trim($css);
    $css_paths = $storage->find_leaf_to_root($subdir,$css);
    if( count($css_paths) > 0 ) {
      $css_path = $css_paths[0];
      $styles->addsts("/".$css_path);
    }
  }
}
 */
?>

<?php
echo $htmlmeta->join("\n")."\n\n";
echo $scripts->join("\n")."\n\n";
echo $styles->join("\n")."\n\n";
?>
</head>
<?php
$classes = $bodyclasses->join(" ");
if( $classes !== "" ) {
  echo "<body class='$classes'>\n";
} else {
  echo "<body>\n";
}?>
<div class="container">
<header>
<?php
  require("TemplateView_Header.php");
?>
</header>
<section class="main">
<?php echo $page_rendered; ?>
</section>
</div>
<footer>Website powered by Purple Tree 2 © John Allsup 2023</footer>
</body>
</html>