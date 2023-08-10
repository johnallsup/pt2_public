<?php
/*
 * Purple Tree 2
 * Copyright John Allsup 2023
 * You may redistribute this software under the terms of the GNU GPL v3.
 * See LICENSE.txt
 */

define("INLINE",true); # this should go in defs later

define('ACCESS',"private");
define('SITE_SHORT_TITLE',"pt-a");
define('SITE_TITLE',"Allsup Private Wiki A v2.1");
define('MAX_FILENAME_LENGTH',64);
# note that cwd is where root/index.php is, not relative to the location of the .php file doing the require
define("FILES_ROOT","../files");
define("VERSIONS_ROOT","../versions");
define("DATA_DIR","../data");
define("RECENT_WRITES_FILE","recent_writes.log");

define("PAGE_EXT","ptmd");
define("DIR_PERMS",0775);
define("FILE_PERMS",0664);
define("HEADER_REGEX","/^#+.*$/m");
define("YOUTUBE_REGEX","/\\[\\[youtube:(.{11})\\]\\]/");
define("WIKIWORD_REGEX",'/\b[A-Z][A-Za-z0-9_]*[A-Z][A-Za-z0-9_]*\b/');
define('URL_REGEX',"/\\b[a-z]+:\/\/[-a-zA-Z0-9@:%._\\+~#=]{1,256}\\.[a-zA-Z0-9()]{1,6}\\b([-a-zA-Z0-9()@:%_\\+.~#?&\\/=]*)/");
define('MD_IMGLINK_REGEX','/(?<=\s|^)!\[.*?\]\(.*?\)/m');
define('MD_LINK_REGEX','/(?<=\s|^)\[.*?\]\(.*?\)/m');
define('DBL_BRACKET_LINK_REGEX','/(?<=\s|^)\[\[([^\]]+)\]\]/m');
define('MD_LINK_QUOTE_REGEX','/(?<=\s|^)\[(.*?)\]\(<(.*?)>\)/m');
define('IMAGE_REGEX','/\.(jpg|jpeg|jfif|png|webp|gif|svg)$/');
define('BRACES_REGEX','/\\{([^}]+)\\}/');

define("BIBLE_REGEX","/^%b (.*?)\s*:- (.*)$/m");
// usage:
// %b Luke 12:25 :- And which of you by being anxious can add a single hour to his span of life?

require_once("detect_mobile.php");