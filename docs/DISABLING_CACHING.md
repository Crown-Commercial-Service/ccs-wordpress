# Disabling Browser Caching

This document gives step-by-step instructions to disable caching on both Google Chrome and Mozilla Firefox, to assist development when using wordpress in local environments.

Also see [Frontend caching doc](https://github.com/Crown-Commercial-Service/ccs-frontend/blob/development/docs/CACHING.md).

## Google Chrome 

To access the developer tools on Google Chrome either:
* Press Ctrl+Shift+I or
* right-click anywhere on the page and select 'Inspect'

* At the very top of the developer tools window select the Network tab
* Check the 'disable cache' checkbox (found directly underneath the tab headings)

## Mozilla Firefox 

To access the developer tools on Mozilla Firefox either: 
* Press Ctrl+Shift+I or 
* right-click anywhere on the page and select 'Inspect Element (Q)'

* At the very top of the developer tools window select the Network tab
* Check the 'disable cache' checkbox (found in the top right corner of the Network tab)

Note that caching will only be disabled whilst developer tools remains open. 