# Purple Tree 2
This is a simple wiki system I wrote for taking notes.
It is inspired by Obsidian, but is intended to run in a browser,
on desktop or mobile, with no need to install software on the
client. It uses the Parsedown parser to transform the source
into the rendered HTML. It preprocesses the source, for example
transforming WikiWord's into links. It is designed to be
quick to edit, and good enough at formatting the results.
It is not a work of software engineering. It pays no heed
to 'industry best practices'. It is designed to be easy
to hack new features into, such as 'block specials'.

I wrote it for use on the Ionos shared hosting package.
Essentially all it requires is a basic LAMP stack,
and doesn't need any SQL database. The indexing uses
a python script. I think Python 3.7 is fine.

It is shared as is for whoever can find a use for it.
I wrote it for me, and it works fine for my purposes.

# Setup
You need to create the folders `data`, `files` and `versions`.
The web server needs write access to these folders.
There is a file `example-localdefs.php` as a template.
You need to edit this and save it as `localdefs.php`.
This is stuff specific to a particular instance of PT2.
The search and indexing is done by a python script.
You can use a cron job to run it every hour.

# Plugin System
The plugin system is simple: you load the source in vim, or vscode,
and plug in whatever you want, wherever you like. There are no abstract
base class or any stuff like that.

# PTMD
The source format is called ptmd, for Purple Tree MarkDown.
It basically preprocesses input for the Parsedown parser.
There are block specials, which take the form of fenced
code blocks. You add them by adding `special_block_blockname`
methods to `ptmd.php`, and then adding CSS and javascript
as required. It is designed to be easily hackable, and
is most definitely not in line with software engineering
best practices. Inline specials take the `[[...]]` syntax
and overload it. If there is a `:` inside the `[[...]]`
then it is interpreted as a special. For example
`[[youtube:<video id>]]` could turn into an embedded
youtube video. Basically, using the 'plugin system'
described above, you 'plug in' code to transform
a special into whatever HTML you want.

# Database
The database is the filesystem, the filesystem is the database.
PT is built around the idea of a hierarchical organisation of
named pages. Basically it is designed to work like a filesystem
and so to keep things simple, it just uses the filesystem.

# Admin Interface
There isn't one. You ssh into the server and use the command line.
To back up the files, you use rsync. To move things, you use `mv`.
You can use symbolic links if you like.

# Security
There is a function `is_auth` defined in `auth.php`.
You need to add your own security code. The `is_auth`
function returns `true` or `false` depending on the
access right. As it is, PT uses only `read` and `write`
permissions. There are three policies: private,
public, and wideopen. A user is either authenticated
or not. An authenticated user can always read, and
always write. On a wideopen site, anybody can write.
On a public site, anybody can read, but only an
authenticated user can write. And that's it.

# Purpose
PT2 is intended to be a *personal* wiki. I wrote it
for my own use. There is a single user, who is either
authenticated or not. There are no user accounts.
