FAQ
---

*Q: Tell me about caching*
A: Here's a nice writeup on caching I found:
http://www.port80software.com/products/cacheright/cachingandcachecontrol

*Q: Ok, I did everything by the book, but no static pages are being generated, what's up?*
A: You are logged into the backend, no pages are cached if you are logged into the backend, just like normal cache. Either logout of the backend (clear your be_typo_user cookie!) or use a different browser to generate frontend hits.

*Q: How does the .htaccess mod_rewrite ruleset work?*
A: The conditions are regex checks. If they are all met, then the rule following them is applied.
Check if the method is GET:
RewriteCond %{REQUEST_METHOD} GET
Make sure the query string is empty
RewriteCond %{QUERY_STRING} ^$
Check if a file exists (-f) for this request uri

.. code-block:: bash

   RewriteCond %{DOCUMENT_ROOT}/typo3temp/tx_ncstaticfilecache/%{HTTP_HOST}/%{REQUEST_URI}index.html -f


If so, rewrite all uri's that meet these conditions to the static file. This is the last rule ( [L] )

.. code-block:: bash

   RewriteRule .* typo3temp/tx_ncstaticfilecache/%{HTTP_HOST}/%{REQUEST_URI} [L]

If none of the conditions are met, mod_rewrite will fall through to the next ruleset.

*Q: Ok, so I logged out of the backend, but I still don't get to see the statically cached pages, what's up?*
A: Hum . . . although you have logged out of the backend, your be_typo_user is still in the browser. The cookie is set by default to expire at the end of the browser session. You need to either restart your browser or go to your browsers cookie management tool and drop the cookie manually.