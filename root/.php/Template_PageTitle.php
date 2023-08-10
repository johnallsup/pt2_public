<?php
/*
 * Purple Tree 2
 * Copyright John Allsup 2023
 * You may redistribute this software under the terms of the GNU GPL v3.
 * See LICENSE.txt
 */
?><h1 class='page-title'><?php echo $headerTitle; ?></h1>
<?php
if( isset($meta['subtitle'] ) ) {
  echo "<h2 class='page-subtitle'>".$meta['subtitle']."</h2>\n";
}
if( ! is_null($navbar_source) ) {
  echo $navbar_rendered; 
}
