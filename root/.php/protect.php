<?php
/*
 * Purple Tree 2
 * Copyright John Allsup 2023
 * You may redistribute this software under the terms of the GNU GPL v3.
 * See LICENSE.txt
 */

class ProtectRegex {
  function __construct() {
    $this->patterns = array();
    $this->protected = array();
    $this->protected_prefix = strtolower(hash("sha256",time().__LINE__));
    $this->protect_count = 0;
  }
  function default_protect($match) {
    return $match[0];
  }
  function add($regex,$callback = null) {
    # the idea here is that preg_replace_callback
    # is run over the source, matches are passed to the callback
    # and the results of that callback are stored in
    # the protection array, and replaced with unique
    # strings that identify them.
    # it is important to ensure that no regex will match
    # one of the temporary string returned.
    # if no callback is provided, the entire matched
    # string is protected, otherwise the callback
    # is allowed to modify it.
    if( is_null($callback) ) {
      $callback = [$this,"default_protect"];
    }
    $protect_callback = function($match) use($callback) {
      return $this->protect($callback($match));
    };
    array_push($this->patterns,[$regex,$protect_callback]);
  }
  private function protect(string $content): string {
    $protid = strtolower(hash("sha256",$this->protect_count++));
    $key = $this->protected_prefix.$protid; # generate unique string
    $this->protected[$key] = $content;
    return $key;
  }
  # to use, once regex's and callbacks are added,
  # do_protect($source)
  # do what you needed protection from
  # un_protect($source)
  function do_protect($source) {
    foreach($this->patterns as $regex_callback) {
      $regex = $regex_callback[0];
      $callback = $regex_callback[1];
      #if( ! $source ) {
      #  //echo "Regex: $regex";
      #  // var_dump(debug_backtrace());
      #}
      $source = preg_replace_callback($regex,$callback,$source);
    }
    return $source;
  }
  function un_protect($source) {
    foreach(array_reverse($this->protected) as $id => $string) {
      $source = str_replace($id,$string,$source);
    }
    return $source;
  }
}
class ProtectString {
  function __construct() {
    $this->patterns = array();
    $this->protected = array();
    $this->protected_prefix = strtolower(hash("sha256",time().__LINE__));
    $this->protect_count = 0;
  }
  function add(string $str) {
    $prot = $this->protect($str);
    array_push($this->patterns,[$str,$prot]);
  }
  private function protect(string $content): string {
    $protid = "PROT".strtolower(hash("sha256",$this->protect_count++));
    $key = $this->protected_prefix.$protid; # generate unique string
    $this->protected[$key] = $content;
    return $key;
  }
  function do_protect($source) {
    foreach($this->patterns as $pattern) {
      $str = $pattern[0];
      $prot = $pattern[1];
      $source = str_replace($str,$prot,$source);
    }
    return $source;
  }
  function un_protect($source) {
    foreach(array_reverse($this->patterns) as $pattern) {
      $str = $pattern[0];
      $prot = $pattern[1];
      $source = str_replace($prot,$str,$source);
    }
    return $source;
  }
}