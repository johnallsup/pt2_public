<?php
/*
 * Purple Tree 2
 * Copyright John Allsup 2023
 * You may redistribute this software under the terms of the GNU GPL v3.
 * See LICENSE.txt
 */

require_once("common.php");
if( ! is_auth("view") ) {
  require("AccessDenied.php");
}
header('Location: /home', true, 303);