1. On old site. Write better header, especially for mobile.
2. Then sort out the Javascript and bring in the new keyboard shortcut system.
1. Only api endpoint left to implement is `api_upload.php`
    1. done
1. We can now store and upload. Next backend to implement is versions.
1. Rewritten CSS factored into `common`, `desktop,mobile` and `desktop,mobile_edit,view`

# TODO
1. search
2. rewrite javascript code
    1. use new keyboard handler modules
    2. We can't inline modules, and we want to inline all javascript
        so as to void too many streams issue. So all `<script>`
        with `src` that does not start with `https?:`, we find
        and inline the file. We can do this with the add function.
3. write editor frontend in React/Vite — add necessary api endpoints

4. Refactor things so that we inline all styles and scripts.
    This will reduce the number of simultaneous streams.
    Only non-inlined scripts will be highlight.js, mathjax and abcjs
