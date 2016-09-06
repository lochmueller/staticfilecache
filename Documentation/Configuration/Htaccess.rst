htaccess file
^^^^^^^^^^^^^

This is the base .htaccess configuration. Please take a look for the default variables (SFC_ROOT, SFC_GZIP) and read the comments carefully.

.. code-block:: bash

   ### Begin: Static File Cache (preparation) ####

   # Document root configuration
   RewriteRule .* - [E=SFC_ROOT:%{DOCUMENT_ROOT}]
   # RewriteRule .* - [E=SFC_ROOT:%{DOCUMENT_ROOT}/t3site] # Example if your installation is installed in a directory

   # Cleanup URI
   RewriteCond %{REQUEST_URI} ^.*$
   RewriteRule .* - [E=SFC_URI:/%{REQUEST_URI}]
   RewriteCond %{REQUEST_URI} ^/.*$
   RewriteRule .* - [E=SFC_URI:%{REQUEST_URI}]
   RewriteCond %{REQUEST_URI} ^/?$
   RewriteRule .* - [E=SFC_URI:/]

   # Get scheme/protocol
   RewriteCond %{SERVER_PORT} ^443$
   RewriteRule .* - [E=SFC_PROTOCOL:https]
   RewriteCond %{SERVER_PORT} !^443$
   RewriteRule .* - [E=SFC_PROTOCOL:http]

   # Set gzip extension into an environment variable if the visitors browser can handle gzipped content.
   RewriteCond %{HTTP:Accept-Encoding} gzip [NC]
   RewriteRule .* - [E=SFC_GZIP:.gz]
   #RewriteRule .* - [E=SFC_GZIP:] # Add this line, to disable the gzip redirect

   # Check if the requested file exists in the cache, otherwise default to index.html that
   # set in an environment variable that is used later on
   RewriteCond %{ENV:SFC_ROOT}/typo3temp/tx_ncstaticfilecache/%{ENV:SFC_PROTOCOL}/%{HTTP_HOST}%{ENV:SFC_URI} !-f
   RewriteRule .* - [E=SFC_FILE:/index.html]

   ### Begin: Static File Cache (main) ####

   # We only redirect URI's without query strings
   RewriteCond %{QUERY_STRING} ^$

   # It only makes sense to do the other checks if a static file actually exists.
   RewriteCond %{ENV:SFC_ROOT}/typo3temp/tx_ncstaticfilecache/%{ENV:SFC_PROTOCOL}/%{HTTP_HOST}%{ENV:SFC_URI}%{ENV:SFC_FILE}%{ENV:SFC_GZIP} -f

   # NO frontend user is logged in. Logged in frontend users may see different
   # information than anonymous users. But the anonymous version is cached. So
   # don't show the anonymous version to logged in frontend users.
   RewriteCond %{HTTP_COOKIE} !nc_staticfilecache [NC]

   # Uncomment the following line if you use MnoGoSearch
   #RewriteCond %{HTTP:X-TYPO3-mnogosearch} ^$

   # We only redirect GET requests
   RewriteCond %{REQUEST_METHOD} GET

   # NO backend user is logged in. Please note that the be_typo_user cookie expires at the end of the browser session.
   # If you have logged out of the TYPO3 backend and are expecting to see cached pages but don't.
   # Please close this browser session first or remove the cookie manually or use another browser to hit your frontend.
   RewriteCond %{HTTP_COOKIE} !be_typo_user [NC]

   # Rewrite the request to the static file.
   RewriteRule .* typo3temp/tx_ncstaticfilecache/%{ENV:SFC_PROTOCOL}/%{HTTP_HOST}%{ENV:SFC_URI}%{ENV:SFC_FILE}%{ENV:SFC_GZIP} [L]

   # Do not allow direct call the cache entries
   RewriteCond %{ENV:SFC_URI} ^/typo3temp/tx_ncstaticfilecache/.*
   RewriteCond %{ENV:REDIRECT_STATUS} ^$
   RewriteRule .* - [F,L]

   ### Begin: Static File Cache (options) ####

   # Set proper content type and encoding for gzipped html.
   <FilesMatch "\.gz">
      <IfModule mod_headers.c>
         Header set Content-Encoding gzip
      </IfModule>
   </FilesMatch>

   # if there are same problems with ForceType, please try the AddType alternative
   # Set proper content type gzipped html
   <FilesMatch "\.html\.gz">
      ForceType text/html
      # AddType "text/html" .gz
   </FilesMatch>
   <FilesMatch "\.xml\.gz">
      ForceType text/xml
      # AddType "text/xml" .gz
   </FilesMatch>
   <FilesMatch "\.rss\.gz">
      ForceType text/xml
      # AddType "text/xml" .gz
   </FilesMatch>

   ### End: Static File Cache ###


If you use the oldschool .htaccess rewrite rules that come with the TYPO3 dummy, then the relevant static file cache configuration should be inserted in the .htaccess file just before these lines:

.. code-block:: bash

   RewriteCond %{REQUEST_FILENAME} !-f
   RewriteCond %{REQUEST_FILENAME} !-d
   RewriteCond %{REQUEST_FILENAME} !-l
   RewriteRule .* index.php [L]

If the TYPO3 Installation isn´t in your root directory (say your site lives in http://some.domain.com/t3site/), then you have to add the '/t3site' part to the configuration snippet. It must be placed right after %{DOCUMENT_ROOT}. Here is the line of the ruleset to illustrate:

.. code-block:: bash

   RewriteRule .* - [E=SFC_ROOT:%{DOCUMENT_ROOT}/t3site]

You are of course free to make the rules as complex as you like.

There might be some files you never want to pull from cache even if they are indexed. For example you might have some custom realurl rules that make your RSS feed accessible as rss.xml. You can skip rewriting to static file with the following condition:

.. code-block:: bash

   RewriteCond %{REQUEST_FILENAME} !^.*\.xml$