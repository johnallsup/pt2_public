<?php
/*
 * Purple Tree 2
 * Copyright John Allsup 2023
 * You may redistribute this software under the terms of the GNU GPL v3.
 * See LICENSE.txt
 */

function truthy(string $x, bool $default=false ) : bool {
  $truthy = array( 
    "1" => true, "0" => false,
    "true" => true, "false" => false, 
    "yes" => true, "no" => false);
  $x = strtolower($x);
  if( array_key_exists($x,$truthy) ) {
    return $truthy[$x];
  }
  if( is_bool($x) ) { return $x; }
  if( is_int($x) ) { return $x; }
  return $default;
}
  