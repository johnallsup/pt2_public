<?php
/*
 * Purple Tree 2
 * Copyright John Allsup 2023
 * You may redistribute this software under the terms of the GNU GPL v3.
 * See LICENSE.txt
 */

function render_action_bar_mtime($wiki) {
  $pagemtime = $wiki->pagemtime;
  if( $pagemtime !== 0 ) {
    $d = new DateTime('@'.$pagemtime);
    $dt = "    ".$d->format('l Y-m-d H:i:s T');
  } else {
    $dt = "    Page '<span class='pagename'>".$wiki->pagename."</span>' does not exist.";
  }
  return "<span class='mtime'>$dt</span>";
}