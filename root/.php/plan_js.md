Rewrite from scratch.

View mostly done (except versions and recent etc)

NEXT: Edit Javascript -- use view js as guide
NEXT: Ajax Upload -- get working on Edit, then copy to view

We have:
1. keyboard shortcuts
2. goto dialog, help dialog, info dialog, preview dialog
3. upload info dialog -- use info dialog -- TODO upload code
4. edit conveniences like paste and such

Also
1. Code to parse query string into map; add/remove; join to query string.
    Write a QueryString class to do this. -- actually only do this if
    we have a lot of querystring handling. If one or two cases just
    write the code where required.

Versions
1. We must inspect GET for `version=` before swithing for `action`.
2. If `action=edit` is specified with `version=X` for nonexistent version,
    then chainload View and set content to "version X or page Y does not exist".
3. done

# general
If the page viewed is a version, the edit should edit that version.
The editor for a version should save as current. Thus the edit
button's href needs to be given `&version=123` with a version.

add a `version=1234` attribute to the body if we are editing a version.
Then we can use CSS to change style to visually indicate we are editing
a version.
