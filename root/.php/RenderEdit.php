<?php
/*
 * Purple Tree 2
 * Copyright John Allsup 2023
 * You may redistribute this software under the terms of the GNU GPL v3.
 * See LICENSE.txt
 */

$navbar_rendered = "<nav>Edit mode navbar -- put controls and stuff here.</nav>";
if( is_mobile )  {
  require("TemplateEdit_Mobile.php");
} else {
  require("TemplateEdit.php");
}