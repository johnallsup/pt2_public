<?php
/*
 * Purple Tree 2
 * Copyright John Allsup 2023
 * You may redistribute this software under the terms of the GNU GPL v3.
 * See LICENSE.txt
 */

class PageSource {
  function __construct($src) {
    # strip off options: and tags: lines (even for .navbar)
    $lines = explode("\n",$src); # strip \r from files when saving
    $options = [];
    $tags = [];
    $meta = [];
    while(count($lines) > 0 && preg_match("/^[a-z]+:/",$lines[0]) ) {
      $line = array_shift($lines);
      [ $k,$v ] = explode(":",$line, 2);
      $v = trim($v);
      if( isset($meta[$k]) ) {
        $meta[$k] .= " ".$v;
      } else {
        $meta[$k] = $v;
      }
    }
    if( isset($meta['options']) ) {
      $xs = preg_split('\s+',trim($meta['options']));
      array_unshift($xs);
      $options = [];
      foreach($xs as $y) {
        $ys = explode("=",$y,2);
        if( count($ys) == 2) {
          $options[$ys[0]] = $ys[1];
        } else {
          $options[$y] = true;
        }
      }
    }
    if( isset($meta['tags']) ) {
      $tags = preg_split('/\s+/',trim($meta['tags']));
    }
    if( isset($meta['title']) ) {
      $this->title = $meta['title'];
    } else {
      $this->title = null;
    }
    $src = ltrim(implode("\n",$lines));
    $this->meta = $meta;
    $this->options = $options;
    $this->tags = $tags;
    $this->src = $src;
  }
}