<?php
/*
 * Purple Tree 2
 * Copyright John Allsup 2023
 * You may redistribute this software under the terms of the GNU GPL v3.
 * See LICENSE.txt
 */


function fmt_time($time) {
  if( $time === 0 ) {
    return [ null, null, null ];
  }
  $now = time();
  $d = new DateTime('@'.$time);
  $dt = $now - $time;
  $mtime_fmt_short = $d->format('m-d H:i:s');
  if( $dt < 24*60*60 ) {
    $h = intval( $dt / 3600 );
    $m = intval( ($dt % 3600) / 60);
    $s = $dt % 60;
    $mt = "";
    if( $h > 0 ) {
      $mt .= $h."h ";
    }
    if( $m > 0 ) {
      $mt .= $m."m ";
    }
    if( $s > 0 ) {
      $mt .= $s."s ";
    }
    if( $mt === "" ) {
      $mt = "right now";
    } else {
      $mt .= "ago";
    }
    $mtime_fmt_short_ago = $mt;
  } else {
    $mtime_fmt_short_ago = $mtime_fmt_short;
  }
  $mtime_fmt_long = $d->format('l Y-m-d H:i:s T');
  return [ $mtime_fmt_long, $mtime_fmt_short, $mtime_fmt_short_ago ];
}
function fmt_pagemtime($pagemtime) { 
  global $wiki;
  global $mtime_fmt_long, $mtime_fmt_short, $mtime_fmt_short_ago;
  [ $mtime_fmt_long, $mtime_fmt_short, $mtime_fmt_short_ago ] = fmt_time($pagemtime);
  if( $mtime_fmt_long === null ) {
    $mtime_fmt_long = "Page '<span class='pagename'>".$wiki->pagename."</span>' does not exist.";
    $mtime_fmt_short = "does not exist";
    $mtime_fmt_ago = "does not exist";
  }
}