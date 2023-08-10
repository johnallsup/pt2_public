<?php
/*
 * Purple Tree 2
 * Copyright John Allsup 2023
 * You may redistribute this software under the terms of the GNU GPL v3.
 * See LICENSE.txt
 */

function write_log($message) {
  $d = new DateTime();
  $dt = $d->format('l Y-m-d H:i:s T');
  $t = "$dt: $message\n";
  file_put_contents("log",$t,FILE_APPEND);
}