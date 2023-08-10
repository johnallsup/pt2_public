<?php
/*
 * Purple Tree 2
 * Copyright John Allsup 2023
 * You may redistribute this software under the terms of the GNU GPL v3.
 * See LICENSE.txt
 */

/*
 * You will need to insert your own security code.
 * There are two permissions that PT uses: "read" and "write".
 * For example, test for the presence of a cookie.
 * I have removed the code I use for pt2.allsup.co,
 * you will have to insert your own.
 */
function is_auth($what) {
  switch(ACCESS) {
  case "wideopen":
    return true;
  case "public":
    switch($what) {
      case "read":
        return true;
      default:
        return false;
    }
  default:
    return false;
  }
}