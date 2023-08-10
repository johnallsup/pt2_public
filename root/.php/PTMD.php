<?php
/*
 * Purple Tree 2
 * Copyright John Allsup 2023
 * You may redistribute this software under the terms of the GNU GPL v3.
 * See LICENSE.txt
 */

require_once("protect.php");
require_once("Parsedown.php");
require_once("truthy.php");

# Parser for PTMD -- most of the work is done by Parsedown
# but we want to turn WikiWords to links provided they occur in text (not headings, nor code, nor maths etc.)
class PTMD {
  # we want a general api for block handlers.
  # we have a map name=>function
  # these are not recursive by default, so if we e.g. want to insert another block,
  # we construct the content and call process_block($name,$content)
  function process_block_script($type,$options,$content) {
    if( $options !== "" ) $options = " ".$options;
    return "<script$options>\n".trim($content)."\n</script>\n";
  }
  function process_block_quote($type,$options,$content) {
    $quotes = explode("\n\n",$content);
    $t = "<div class='quotes1'>\n";
    foreach($quotes as $quote) {
      if( preg_match("/^(.*)\s+---\s+(.*?)$/s",$quote,$m) ) {
        [ $all, $text, $author ] = $m;
        $text = trim($text);
        $t .= "<p class='quote'><span class='quote-mark'>&#x201C;</span><span class='quote-text'>$text</span> <span class='quote-mark'>&#x201D;</span>&mdash; <span class='author'>$author</span></p>\n";
      } else {
        $t .= "<p class='quote'><span class='quote-text'>$quote</span></p>\n";
      }
    }
    $t .= "</div>";
    return $t;
  }
  function process_block_plain($type,$options,$content)  {
    $xs = explode(":",$options,2);
    $cls = "pre-wrap plain";
    if( count($xs) === 2 ) {
      $cs = trim($xs[0]);
      if( $cs !== "" ) {
        $cls .= " ".$cs;
      }
      $opts = trim($xs[1]);
    } else {
      $opts = trim($options);
    }
    if( $opts !== "" ) { $opts = " ".$opts; }
    return "<div class='$cls'$opts>$content\n</div>";
  }
  function process_block_style($type,$options,$content) {
    if( $options !== "" ) $options = " ".$options;
    return "<style$options>\n".trim($content)."\n</style>\n";
  }
  function process_block_abc($type,$options,$content) {
    $this->uses["abc"] = true; 
    $content = trim($content);
    return "<div class='abc'>\n$content\n</div>\n";
  }
  function process_block_abcd($type,$options,$content) {
    $options = trim($options);
    $content = trim($content);
    $a = "";
    $a .= "X:1\n";
    $a .= "L:1/4\n";
    if( $options !== "" ) {
      $a .= "T:".$options."\n";
    }
    $a .= "M:4/4
K:C
$content";
    return $this->process_block_abc("abc","",$a);
  }
  function __construct($wiki) {
    $this->wiki = $wiki;
    $this->parsedown = new Parsedown();
    $this->uses = [];
  }
  function get_option_bool($optname,$default=false) {
    $options = &$this->options;
    return truthy(array_get($options,$optname,$default),$default);
  }
  function WikiWord_to_link($match) {
    $word = $match[0];
    if( preg_match("/[a-z]/",$word) ) {
      return "[$word]($word)";
      #return "<span class='WikiWord'>$word</span>";
    } else {
      return $word;
    }
  }
  function render($source,$config,$options,$wikiwords=true) {
    global $block_handlers;
    $this->config = &$config;
    $this->options = &$options;
    $x = $source;
    # protect from WikiWords and transform things like [[these]]

    $protect = new ProtectRegex();
    
    # protect->add_block("nohightlight",$callback,"nohighlight blocks);
    $protect->add("/^(```nohighlight\\s(.*?)^```)/ms",function($match) {
              return "<div class='nohightlight'>$match[2]</div>"; });
    # options need to be true/false/auto
    # if true or auto, add this rule
    # use($options) and if pattern happens, set $options[$abc"] = "true"
    # has issues for truthy
    # later replace this with a more flexible
    # and extensible block transfers system
    # ```blocktype args....\n\n```
    # ```blocktype(XX) args....\n\nXX``` # arbitrary delimiter
    
    $uses = &$this->uses;
    $protect->add('/^(```+)(.*?)?^\1/ms',function($match) use(&$uses) { 
      [ $block ] = $match;
      if( ! preg_match('/^(```+)(\w+)(.*?)$(.*)^\1/ms',$block,$m) ) {
        return $block;
      }
      [ $all, $ticks, $type, $options, $content ] = $m;
      $options = trim($options);

      if( method_exists($this,$method="process_block_$type") ) {
        $block = $this->$method($type,$options,$content);
      }

      return $block;
    });
                
    #$protect->add("/^(```(.*?)^```)/ms");
    $protect->add("/(`+)(.*?)(\\1)/s");

    # ![alt text]{img opts}(img src){a opts}(a href)
    $protect->add('/!\[([^\]]*)\]\{([^}]*)\}\(([^)]*)\)(?:\{([^}]*)\})?(?:\((.*)\))/',function($match) {
      [ $all, $name, $opts, $src, $hopts, $href ] = $match;
      $opts = trim($opts);
      $xs = explode(":",$opts,2);
      if( count($xs) == 2 ) {
        $opts = "class=\"$xs[0]\"";
        if( $xs[1] !== "" ) {
          $opts .= " ".$xs[1];
        }
      } 
      if( $opts !== "" ) { $opts = " ".$opts; }
      $xs = explode(":",$hopts,2);
      if( count($xs) == 2 ) {
        $hopts = "class=\"$xs[0]\"";
        if( $xs[1] !== "" ) {
          $hopts .= " ".$xs[1];
        }
      } 
      if( $hopts !== "" ) { $hopts = " ".$hopts; }
      return "<a href=\"$href\"$hopts><img src=\"$src\"$opts/></a>";
    });
    $protect->add('/!\[([^\]]*)\]\{([^}]*)\}\(([^)]*)\)/',function($match) {
      [ $all, $name, $opts, $src ] = $match;
      $opts = trim($opts);
      $xs = explode(":",$opts,2);
      if( count($xs) == 2 ) {
        $opts = "class=\"$xs[0]\"";
        if( $xs[1] !== "" ) {
          $opts .= " ".$xs[1];
        }
      } 
      if( $opts !== "" ) { $opts = " ".$opts; }
      return "<img src=\"$src\"$opts/>";
    });
    $protect->add('/!\[([^\]]*)\]\{([^}]*)\}\(([^)]*)\)/',function($match) {
      [ $all, $name, $opts, $href ] = $match;
      $opts = trim($opts);
      $xs = explode(":",$opts,2);
      if( count($xs) == 2 ) {
        $opts = "class=\"$xs[0]\"";
        if( $xs[1] !== "" ) {
          $opts .= " ".$xs[1];
        }
      } 
      if( $opts !== "" ) { $opts = " ".$opts; }
      return "<img src=\"$href\"$opts/>";
    });
    $protect->add('/\[([^\]]*)\]\{([^}]*)\}\(([^)]*)\)/',function($match) {
      [ $all, $name, $opts, $href ] = $match;
      $opts = trim($opts);
      $xs = explode(":",$opts,2);
      if( count($xs) == 2 ) {
        $opts = "class=\"$xs[0]\"";
        if( $xs[1] !== "" ) {
          $opts .= " ".$xs[1];
        }
      } 
      if( $opts !== "" ) { $opts = " ".$opts; }
      return "<a href=\"$href\"$opts>$name</a>";
    });

    $re_bracket = '/\\\\\\[.*?\\\\\\]/s';
    $re_paren = '/\\\\\\(.*?\\\\\\)/s';
    $options["math"] = false;
    # worth adding a 'uses' flag, like 'math', so that
    # protect will handle adding entries indicating what's been used
    
    $protect->add($re_bracket,function($m) use(&$uses) { $uses["math"] = true; return $m[0];});
    $protect->add($re_paren,function($m) use(&$uses) { $uses["math"] = true; return $m[0];});

    $protect->add('@<a .*?</a>@is',function($match) { return $match[0]; } );
    //$source = preg_replace_callback(BIBLE_REGEX,[$this,"protect_bible"],$source);
    $protect->add(BIBLE_REGEX,function($match) {
      [$m,$ref,$text] = $match;
      return "<p class='bible_quote'><span class='ref'>$ref</span>&nbsp;<span class='text'>$text</span></p>";
    });
    $protect->add(HEADER_REGEX);
    //$protect->add(YOUTUBE_REGEX);
    $protect->add(DBL_BRACKET_LINK_REGEX,function($match) {
      # detect youtube links and other things of the form [[something:else]]
      $a = $match[1];
      $b = explode(":",$a,2);
      if( count($b) > 1 ) {
        switch($b[0]) {
          case "youtube":
          case "y":
            return "<iframe width='420' height='315' src='https://www.youtube.com/embed/$b[1]'></iframe>";
          case "test":
            return "TEST **$b[0]**:*$b[1]*";
          case "dir":
            return $this->makeDirOf($this->wiki->path,$b[1]);
          default:
            return "[DEFAULT **$b[0]**:*$b[1]*](".urlencode($b[2]).")";
        }
      } else {
        $ae = urlencode($a);
        $ae = str_replace("%2F","/",$ae); # we don't want to escape slashes in links
        return "[$a]($ae)";
      }
    });
    $protect->add(MD_IMGLINK_REGEX);
    $protect->add(MD_LINK_QUOTE_REGEX,function($match) {
        pre_dump("Link quote regex",$match);
        return "[$match[1]]($match[2])"; });
    $protect->add(MD_LINK_REGEX);
    $protect->add(URL_REGEX);
    $protect->add(BRACES_REGEX, function($match) { return $match[1]; });

    $x = $protect->do_protect($x);
    # do_protect will do all other transforms even if we don't want WikiWords
    # apply WikiWord transform
    if( $wikiwords ) {
      # TODO we're going to replace WikiWords with some client-side Javascript
      $x = preg_replace_callback(WIKIWORD_REGEX,[$this,"WikiWord_to_link"],$x);
    }
    # unprotect
    $x = $protect->un_protect($x);

    # protect from Parsedown
    $protect = new ProtectRegex();
    
    if( $this->get_option_bool("abc",true) ) {
      $protect->add("/^(?:```abc\\s(.*?)^```)/ms",function($match) {
        return "<div class='abc'>\n$match[1]\n</div>";
      });
    }
    if( $this->get_option_bool("math",true) ) {
      $re_bracket = '/\\\\\\[.*?\\\\\\]/s';
      $re_paren = '/\\\\\\(.*?\\\\\\)/s';
      $protect->add($re_bracket);
      $protect->add($re_paren);
    }

    $x = $protect->do_protect($x);

    # apply Parsedown->text
    $x = $this->parsedown->text($x);

    # unprotect
    $x = $protect->un_protect($x);

    # done
    return $x;
  }
  function fmt_dir_ol($xs) {
    $t = "\n";
    foreach($xs as $x) {
      $t .= "1. $x\n";
    }
    $t .= "\n\n";
    #var_dump($t);
    return $t;
  }
  function fmt_dir_ul($xs) {
    $t = "\n";
    foreach($xs as $x) {
      $t .= "* $x\n";
    }
    $t .= "\n\n";
    #var_dump($t);
    return $t;
  }
  function makeDirOf($path,$opts) {
    $storage = $this->wiki->storage;
    $dirname = dirname($path);
    if( $dirname == "." ) { $dirname = ""; }
    $dirhandler = new WikiHandlerDir($this->wiki);
    $dirhandler->get_dir_contents();
    $dirs = $dirhandler->dirs;
    $pages = $dirhandler->pages;
    $files = $dirhandler->files;
    $result = [];
    $o = ["pages"=>false,"dirs"=>false,"files"=>false,"images"=>false,"regex"=>null,"fmt"=>null,"except"=>null];
    $os = preg_split("/\s*,\s*/",trim($opts));
    
    foreach($os as $ox) {
      $xs = explode("=",$ox,2);
      if( count($xs) == 1 ) {
        array_push($xs,true);
      } else {
        if( ! preg_match('/^(regex|except)$/',$xs[0]) ) {
          $xs[1] = truthy($xs[1],false);
        }
      }
      $o[$xs[0]] = $xs[1];
    }
    if( $o["pages"] ) {
      foreach($pages as $page) {
        $page = preg_replace('/\.ptmd$/','',$page);
        if( is_string($o['except']) && preg_match("/".$o['except']."/",$page ) ) {
          continue;
        }
        if( is_string($o['regex']) ) {
          if( preg_match("/".$o['regex']."/",$page ) ) {
            array_push($result,"[$page](".urlencode($page).")");
          }
        } else {
          array_push($result,"[$page](".urlencode($page).")");
        }
      }
    }
    if( $o["dirs"] ) {
      foreach($dirs as $dir) {
        if( is_null($o['regex']) || preg_match("/".$o['regex']."/",$dir ) ) {
          array_push($result,"[$dir](".urlencode($dir).")");
        }
      }
    }
    if( $o["files"] ) {
      foreach($files as $file) {
        if( is_null($o['regex']) || preg_match("/".$o['regex']."/",$file ) ) {
          array_push($result,"[$file](".urlencode($file).")");
        }
      }
    }
    if( $o["images"] ) {
      foreach($files as $file) {
        if( preg_match(IMAGE_REGEX,$file) ) {
          if( is_null($o['regex']) || preg_match("/".$o['regex']."/",$file ) ) {
            array_push($result,"[$file](".urlencode($file).")");
          }
        }
      }
    }
    $fmt = "fmt_dir_".$o['fmt'];
    if( method_exists($this,$fmt) ) {
      #var_dump($this->$fmt($result));
      return $this->$fmt($result);
    }
    return implode(" ",$result);
  }
}