<?php
/*
 * Purple Tree 2
 * Copyright John Allsup 2023
 * You may redistribute this software under the terms of the GNU GPL v3.
 * See LICENSE.txt
 */

namespace \PT;

# TODO these are for debugging output and not required in final product

function pre() {
  echo "<pre>";
}
function epre() {
  echo "\n</pre>";
}
function list_begin() { echo "<ul style='list-style-type:none'>\n"; }
function list_end() { echo "</ul>"; }