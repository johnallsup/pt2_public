<?php
/*
 * Purple Tree 2
 * Copyright John Allsup 2023
 * You may redistribute this software under the terms of the GNU GPL v3.
 * See LICENSE.txt
 */


error_reporting(E_ALL);
ini_set('display_errors', '1');

$docroot = $_SERVER['DOCUMENT_ROOT'];

require_once("defs.php");
require_once("utils.php");
require_once("wiki.php");
require_once("cors.php");
require_once("versioned_storage.php");
require_once("auth.php");
require_once("dir_config.php");