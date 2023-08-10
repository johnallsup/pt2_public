#!/usr/bin/env python3
import sys,os,os.path,re
from glob import glob
from collections import defaultdict
from pathlib import Path
import json
import re


args = sys.argv[1:]
if "-v" in args:
  verbose = True
else:
  verbose = False
def p(*xs,**kw):
  if verbose:
    print(*xs,**kw)
verbose = False # TODO remove

links_out = defaultdict(set)
links_in = defaultdict(set)
by_tag = defaultdict(set)
by_word_case_sensitive = defaultdict(set)
by_word_ignore_case = defaultdict(set)
by_page_name = defaultdict(set)
all_words = set()
all_tags = set()
pages = []
files = []
pages_mtimes = []
files_mtimes = []
dirs = set()
files_location = "../files"
data_location = "../data"
def dp(fn):
  return data_location+"/"+fn

def path_relative_to_files_root(path):
  return path[len(files_location)+1:]

def handleLink(m,links):
  a = m.group()
  p(f"Link: {a}")
  if a[0] == "[":
    try:
      if a[1] == "[":
        s = a[2:-2]
      else:
        s = a.split("(")[1][:-1]
    except IndexError:
      p(f"#Fail {a}")
      exit()
  else:
    if not re.search(r"[a-z]",a):
      return
    s = a
  if ":" in s:
    return
  links.add(s)

class P:
  def __init__(self):
    self.x = None
  def __call__(self,x):
    self.x = x
    return x
pocket = P()

def procpage(path):
  p(f"Page: {path}")
  mtime = os.path.getmtime(path)
  rpath = path_relative_to_files_root(path)
  pages_mtimes.append((mtime,rpath))
  path_components = rpath.split("/")
  pagename = path_components[-1]
  if len(path_components) > 1: 
    dirname = "/".join(path_components[:-1])
    dirs.add(path_relative_to_files_root(dirname))
  else:
    dirname = ""
  src = open(path).read().strip()
  src = src.replace("\r","")
  lines = src.splitlines()
  if len(lines) == 0:
    return
  meta = {}
  while len(lines) > 0 and pocket(re.match(r"([a-z]+):\s+(.*)",lines[0])):
    m = pocket.x
    k,v = m.groups()
    meta[k] = v
    lines = lines[1:]
  if "tags" in meta:
    tags = meta["tags"]
    tags = re.split(r"[^a-zA-Z0-9_-]+",tags)
    for tag in tags:
      if len(tag) == 0:
        continue
      tag = tag.lower()
      tag = tag.lstrip("#")
      all_tags.add(tag)
      by_tag[tag].add(rpath)
    lines = lines[1:]
  for line in lines:
    line = re.sub(r"[^a-zA-Z0-9_-]+"," ",line).strip()
    words = line.split(" ")
    for word in words:
      if len(word) == 0:
        continue
      all_words.add(word)
      by_word_case_sensitive[word].add(rpath)
      p(path,word,by_word_case_sensitive[word])
      word = word.lower()
      by_word_ignore_case[word].add(rpath)
  # process links
  src = "\n".join(lines)
  src = re.sub(r"^(`{3,}).*?^\1"," BLOCK ",src)
  src = re.sub(r"(`+).*?\1"," CODE ",src)
  links = set()
  for m in re.finditer(
      r"\[\[.*?\]\]|\[[^\]]*\]\([^)]+\)|[A-Z][A-Za-z0-9_]*[A-Z][A-Za-z0-9_]*",
      src):
    p(f"Match m={m} m.group()={m.group()}")
    handleLink(m,links)
  for link in links:
    if not link.startswith("/"):
      if len(dirname) > 0:
        link = dirname+"/"+link
    links_out[rpath].add(link)
    links_in[link].add(rpath)
  pages.append(rpath)
  by_page_name[pagename]

def procfile(path):
  rpath = path_relative_to_files_root(path)
  mtime = os.path.getmtime(path)
  pages_mtimes.append((mtime,rpath))
  files.append(rpath)

def dump_json(obj,filename):
  with open(filename,"wt") as f:
    json.dump(obj,f)

def main():
  for path in Path(files_location).rglob("*.*"):
    path = str(path)
    rpath = path_relative_to_files_root(path)
    p(f"{path}:({rpath})")
    path_components = rpath.split("/")
    fn = path_components[-1]
    if len(path_components) > 1: 
      dirname = "/".join(path_components[:-1])
      dirs.add(dirname)
    if path.endswith(".ptmd"):
      # page
      procpage(path)
    else:
      p(f"File: {path}")
      procfile(path)

  by_tag_l = { k:list(v) for k,v in by_tag.items() }
  by_word_cs = { word:list(sorted(pages)) for word,pages in by_word_case_sensitive.items() }
  by_word_ic = { word:list(sorted(pages)) for word,pages in by_word_ignore_case.items() }
  links_out_a = { pagename:list(sorted(links)) for pagename,links in links_out.items() }
  links_in_a = { pagename:list(sorted(links)) for pagename,links in links_in.items() }
  dump_json(list(sorted(all_words)),dp("all_words.json"))
  dump_json(list(sorted(all_tags)),dp("all_tags.json"))
  dump_json(by_tag_l,dp("by_tag.json"))
  dump_json(by_word_cs,dp("by_word_cs.json"))
  dump_json(by_word_ic,dp("by_word_ic.json"))
  dump_json(pages,dp("pages.json"))
  dump_json(files,dp("files.json"))
  dirs_list = [x for x in dirs if x != "" ]
  dump_json(list(sorted(dirs_list)),dp("dirs.json"))
  dump_json(links_out_a,dp("links_out.json"))
  dump_json(links_in_a,dp("links_in.json"))
  p(f"Tags: {', '.join(sorted(all_tags))}")
  p(f"Words: {', '.join(sorted(all_words))}")

  # recent
  recent_pages = list(sorted(pages_mtimes,key=lambda t: -t[0]))
  recent_files = list(sorted(files_mtimes,key=lambda t: -t[0]))
  dump_json(recent_pages,dp("recent_pages.json"))
  dump_json(recent_files,dp("recent_files.json"))
   
  clear_up_very_recent()

def clear_up_very_recent():
  vrfn = dp("recent_writes.log")
  if os.path.exists(vrfn):
    p(f"Removing {vrfn}")
    os.unlink(vrfn)

if __name__ == "__main__":
  main()

