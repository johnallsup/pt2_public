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
require_once("defs.php");

# Parser for PTMD -- most of the work is done by Parsedown
# but we want to turn WikiWords to links provided they occur in text (not headings, nor code, nor maths etc.)

# We need storage to get directories.
# So we do want a $wiki object to accumulate stuff.
class PTMD {
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
  # Inline Specials
  function special_inline_youtube($what,$args) {
    return "<iframe width='420' height='315' src='https://www.youtube.com/embed/$args'></iframe>";
  }
  # Block Specials
  function special_block_duolingo($what,$options,$content) {
    $t = "";
    $options = trim($options);
    $tlang = "";
    $slang = "";
    if( preg_match('/(\S+)\s+(\S+)/',$options,$m) ) {
      [ $all, $tlang, $slang ] = $m;
      $tlang = "<span class='target-lang'>$tlang</span>: ";
      $slang = "<span class='target-lang'>$slang</span>: ";
    }
    $sentences = preg_split('/\n{2,}/',$content);
    $t .= "<div class='duolingo-sentences'>\n";
    foreach($sentences as $s) {
      $lines = explode("\n",$s);
      $t .= "<div class='duolingo-sentence'>\n";
      $ts = array_shift($lines);
      $t .= "<div class='duolingo-target-sentence'>$tlang<span class='sentence'>$ts</span></div>\n";
      if( count($lines) > 0 ) {
        $ss = array_shift($lines);
        $t .= "<div class='duolingo-source-sentence'>$slang<span class='sentence'>$ss</span></div>\n";
      }
      if( count($lines) > 0 ) {
        $s = implode("\n",$lines);
        $t .= "<p class='duolingo-comment'>$s</p>\n";
      }
      $t .= "</div>\n";
    }
    $t .= "</div>\n";
    return $t;
  }
  function special_block_langue1($what,$options,$content) {
    if( trim($content) === "" ) return "";
    $paras = preg_split('/\n{2,}/',trim($content));
    $out_paras = "";
    foreach($paras as $para) {
      $out_para = "<table class='langue1-table'>\n";
      $lines = explode("\n",$para);
      foreach($lines as $line) {
        if( $line[0] === "#" ) {
          $line = trim(substr(ltrim($line,"#"),1));
          $out_para .= "<tr><td class='heading' colspan='2'>$line</td></tr>\n";
        } else if( preg_match('/^(.*?)\s+---\s+(.*)$/',$line,$m) ) {
          [ $all, $for, $eng ] = $m;
          $out_para .= "<tr class='langue-item'><td class='foreign'>$for</td><td class='english'>$eng</td></tr>\n";
        } else {
          $out_para .= "<tr class='langue-item'><td class='foreign' colspan='2'>$line</td></tr>\n";
        }
      }
      $out_para .= "</table>\n";
      $out_paras .= $out_para;
    }
    return "<div class='langue1'>\n$out_paras</div>\n";
  }
  function special_block_keyboardshortcuts($what,$options,$content) {
    $lines = explode("\n",trim($content));
    $t = "<table class='keyboard-shortcuts'>\n";
    foreach($lines as $line) {
      if( preg_match("/^(.*?)\s+---\s+(.*)$/",$line,$m) ) {
        [ $all, $combo, $desc ] = $m;
        $t .= "<tr><td class='combo'>$combo</td><td class='description'>$desc</td></tr>\n";
      } else {
        $t .= "<tr><td class='comment' colspan='2'>$line</td></tr>\n";
      }
    }
    $t .= "</table>\n";
    return $t;
  }
  function special_block_poem($what,$options,$content) {
    $meta = [];
    $lines = explode("\n",trim($content));
    while( count($lines) > 0 && preg_match("/: /",$lines[0]) ) {
      array_push($meta,array_shift($lines));
    }
    $content = implode("\n",$lines);
    $verses = preg_split('/\n{2,}/',trim($content));
    $verses = array_map(function($x) { return "<p class='verse'>$x</p>"; },$verses);
    $verses = implode("\n",$verses);
    if( count($meta) > 0 ) {
      $t = "<div class='meta poem-meta'>\n";
      foreach($meta as $m) {
        [ $k, $v ] = explode(":",$m,2);
        $v = trim($v);
        $t .= "<div class='meta-item'><span class='key'>$k</span>: <span class='value'>$v</span></div>\n";
      }
      $t .= "</div>";
      $meta = $t;
    } else {
      $meta = "";
    }

    return "<div class='poem block-special'>\n".$meta.$verses."\n</div>";
  }
  function special_block_fuck($what,$options,$content) {
    return "FUCK($what,$options,$content)";
  }
  function special_block_script($type,$options,$content) {
    if( $options !== "" ) $options = " ".$options;
    return "<script$options>\n".trim($content)."\n</script>\n";
  }
  function special_block_style($type,$options,$content) {
    if( $options !== "" ) $options = " ".$options;
    return "<style$options>\n".trim($content)."\n</style>\n";
  }
  function special_block_quotes1($type,$options,$content) {
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
  function special_block_plain($type,$options,$content)  {
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
  function special_block_abc($type,$options,$content) {
    $this->uses["abc"] = true; 
    $content = trim($content);
    return "<div class='abc'>\n$content\n</div>\n";
  }
  function special_block_abcd($type,$options,$content) {
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
    return $this->special_block_abc("abc","",$a);
  }
  # Renderer
  function render($source,$options,$wikiwords=true) {
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
      if( ! preg_match('/^(```+)(\S+)(.*?)$(.*)^\1/ms',$block,$m) ) {
        return $block;
      }
      [ $all, $ticks, $what, $options, $content ] = $m;
      $content = trim($content);
      $options = trim($options);

      if( preg_match('/^[A-Za-z]/',$what) ) {
        if( method_exists($this,$method="special_block_$what") ) {
          $block = $this->$method($what,$options,$content);
        }
      } else {
        $what_e = preg_replace('/^[a-zA-Z0-9_\.@#?-]/','_',$what);
        $all = htmlentities($all);
        $options_e = htmlentities($options);
        return "<div class='special block-special client-side-block' special='$what_e'><span class='options'>$options_e</span><div class='block-content'>$content</div></div>";
      }

      return $block;
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
    $this->special_inline_shorthands = [
      "y" => "youtube"
    ];
    $protect->add(DBL_BRACKET_LINK_REGEX,function($match) use(&$uses) { 
      $a = $match[1];
      $b = explode(":",$a,2);
      if( preg_match('/^([^:]+):(.*)$/',$a,$m) ) {
        $what = $m[1];
        $args = $m[2];
        if( array_key_exists($what,$this->special_inline_shorthands) ) {
          $what = $this->special_inline_shorthands[$b[0]];
        }
        if( preg_match('/^[A-Za-z]/',$what) ) {
          if( method_exists($this,$method="special_inline_$what") ) {
            return $this->$method($what,$args);
          }
        } else {
          $what_e = preg_replace('/^[a-zA-Z0-9_\.@#?-]/','_',$what);
          $all = htmlentities($a);
          return "<span class='special inline-special client-side-inline' special='$what_e'>$all</span>";
        }
      }
      # we fall through if the special isn't matched
      $a_encoded = urlencode($a);
      $a_encoded = str_replace("%2F","/",$a_encoded); # we don't want to escape slashes in links
      return "[$a]($a_encoded)";
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

  /// DIR STUFF
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