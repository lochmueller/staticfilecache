.. include:: /Includes.rst.txt

htaccess file
^^^^^^^^^^^^^

This is the base .htaccess configuration. Please take a look for the default
variables (SFC_ROOT, SFC_GZIP) and read the comments carefully.

.. code-block:: apache

   ### Begin: StaticFileCache (preparation) ####

   # Document root configuration
   RewriteRule .* - [E=SFC_ROOT:%{DOCUMENT_ROOT}]
   # RewriteRule .* - [E=SFC_ROOT:%{DOCUMENT_ROOT}/t3site] # Example if your installation is installed in a directory
   # NOTE: There are cases (apache versions and configuration) where DOCUMENT_ROOT do not exists. Please set the SFC_ROOT to the right directory without DOCUMENT_ROOT then!

   # Cleanup URI
   RewriteCond %{REQUEST_URI} ^.*$
   RewriteRule .* - [E=SFC_URI:/%{REQUEST_URI}]
   RewriteCond %{REQUEST_URI} ^/.*$
   RewriteRule .* - [E=SFC_URI:%{REQUEST_URI}]
   RewriteCond %{REQUEST_URI} ^/?$
   RewriteRule .* - [E=SFC_URI:/]

   # Cleanup HOST
   RewriteCond %{HTTP_HOST} ^([^:]+)(:[0-9]+)?$
   RewriteRule .* - [E=SFC_HOST:%1]

   # Disable cache for EXT:solr indexing requests
   RewriteCond %{HTTP:X-Tx-Solr-Iq} .+
   RewriteRule .* - [E=SFC_HOST:invalid-host]

   # Get scheme
   RewriteRule .* - [E=SFC_PROTOCOL:http]
   RewriteCond %{SERVER_PORT} ^443$ [OR]
   RewriteCond %{HTTP:X-Forwarded-Proto} https
   RewriteRule .* - [E=SFC_PROTOCOL:https]

   # Get port
   RewriteRule .* - [E=SFC_PORT:80]
   RewriteCond %{ENV:SFC_PROTOCOL} ^https$ [NC]
   RewriteRule .* - [E=SFC_PORT:443]
   RewriteCond %{SERVER_PORT} ^[0-9]+$
   RewriteRule .* - [E=SFC_PORT:%{SERVER_PORT}]
   RewriteCond %{HTTP:X-Forwarded-Port} ^[0-9]+$
   RewriteRule .* - [E=SFC_PORT:%{HTTP:X-Forwarded-Port}]

   # Full path for redirect
   RewriteRule .* - [E=SFC_FULLPATH:typo3temp/tx_staticfilecache/%{ENV:SFC_PROTOCOL}_%{ENV:SFC_HOST}_%{ENV:SFC_PORT}%{ENV:SFC_URI}/index]

   # Extension (Order: br, gzip, default)
   RewriteRule .* - [E=SFC_EXT:]
   RewriteCond %{HTTP:Accept-Encoding} br [NC]
   RewriteRule .* - [E=SFC_EXT:.br]
   RewriteCond %{ENV:SFC_ROOT}/%{ENV:SFC_FULLPATH}%{ENV:SFC_EXT} !-f
   RewriteRule .* - [E=SFC_EXT:]
   RewriteCond %{ENV:SFC_EXT} ^$
   RewriteCond %{HTTP:Accept-Encoding} gzip [NC]
   RewriteRule .* - [E=SFC_EXT:.gz]
   RewriteCond %{ENV:SFC_EXT} ^\.gz$
   RewriteCond %{ENV:SFC_ROOT}/%{ENV:SFC_FULLPATH}%{ENV:SFC_EXT} !-f
   RewriteRule .* - [E=SFC_EXT:]

   # Write Extension to SFC_FULLPATH
   RewriteRule .* - [E=SFC_FULLPATH:%{ENV:SFC_FULLPATH}%{ENV:SFC_EXT}]

   ### Begin: StaticFileCache (main) ####

   # We only redirect URI's without query strings
   RewriteCond %{QUERY_STRING} ^$

   # It only makes sense to do the other checks if a static file actually exists.
   RewriteCond %{ENV:SFC_ROOT}/%{ENV:SFC_FULLPATH} -f

   # NO frontend or backend user is logged in. Logged in users may see different
   # information than anonymous users. But the anonymous version is cached. So
   # don't show the anonymous version to logged in users.
   RewriteCond %{HTTP_COOKIE} !staticfilecache [NC]

   # We only redirect GET requests
   RewriteCond %{REQUEST_METHOD} GET

   # Rewrite the request to the static file.
   RewriteRule .* %{ENV:SFC_ROOT}/%{ENV:SFC_FULLPATH} [L]

   # Do not allow direct call the cache entries
   RewriteCond %{ENV:SFC_URI} ^/typo3temp/tx_staticfilecache/.*
   RewriteCond %{ENV:REDIRECT_STATUS} ^$
   RewriteRule .* - [F,L]

   # Handle application cache
   RewriteCond %{REQUEST_FILENAME} !-f
   RewriteCond %{REQUEST_FILENAME} !-d
   RewriteCond %{REQUEST_FILENAME} !-l
   RewriteRule ^.*\.sfc$ %{ENV:CWD}index.php?eID=sfc_manifest [QSA,L]

   ### Begin: StaticFileCache (options) ####

   # Set proper content type and encoding for gzipped html.
   <FilesMatch "\.gzip$">
      SetEnv no-gzip 1
      SetEnv no-brotli 1
      <IfModule mod_headers.c>
         Header set Content-Encoding gzip
      </IfModule>
   </FilesMatch>
   <FilesMatch "\.gz$">
      SetEnv no-gzip 1
      SetEnv no-brotli 1
      <IfModule mod_headers.c>
         Header set Content-Encoding gzip
      </IfModule>
   </FilesMatch>
   <FilesMatch "\.br$">
      SetEnv no-gzip 1
      SetEnv no-brotli 1
      <IfModule mod_headers.c>
         Header set Content-Encoding br
      </IfModule>
   </FilesMatch>

   # if there are same problems with ForceType, please try the AddType alternative
   # Set proper content type gzipped html
   <FilesMatch "\.gzip$">
      ForceType text/html
      # AddType "text/html" .gzip
   </FilesMatch>
   <FilesMatch "\.js\.gzip$">
      ForceType text/javascript
      # AddType "text/javascript" .gzip
   </FilesMatch>
   <FilesMatch "\.css\.gzip$">
      ForceType text/css
      # AddType "text/css" .gzip
   </FilesMatch>
   <FilesMatch "\.xml\.gzip$">
      ForceType text/xml
      # AddType "text/xml" .gzip
   </FilesMatch>
   <FilesMatch "\.rss\.gzip$">
      ForceType text/xml
      # AddType "text/xml" .gzip
   </FilesMatch>
   <FilesMatch "\.gz$">
      ForceType text/html
      # AddType "text/html" .gz
   </FilesMatch>
   <FilesMatch "\.xml\.gz$">
      ForceType text/xml
      # AddType "text/xml" .gz
   </FilesMatch>
   <FilesMatch "\.rss\.gz$">
      ForceType text/xml
      # AddType "text/xml" .gz
   </FilesMatch>
   <FilesMatch "\.br$">
      ForceType text/html
      # AddType "text/html" .br
   </FilesMatch>
   <FilesMatch "\.xml\.br$">
      ForceType text/xml
      # AddType "text/xml" .br
   </FilesMatch>
   <FilesMatch "\.rss\.br$">
      ForceType text/xml
      # AddType "text/xml" .br
   </FilesMatch>

   # Avoid .br files being delivered with Content-Language: br headers
   <IfModule mod_mime.c>
      RemoveLanguage .br
   </IfModule>

   ### End: StaticFileCache ###


If you use the oldschool .htaccess rewrite rules that come with the TYPO3 dummy,
then the relevant StaticFileCache configuration should be inserted in the
.htaccess file just before these lines:

.. code-block:: apache

   RewriteCond %{REQUEST_FILENAME} !-f
   RewriteCond %{REQUEST_FILENAME} !-d
   RewriteCond %{REQUEST_FILENAME} !-l
   RewriteRule .* index.php [L]

If the TYPO3 Installation isn´t in your root directory (say your site lives in
:samp:`https://some.domain.com/t3site/`), then you have to add the '/t3site'
part to the configuration snippet. It must be placed right after %{DOCUMENT_ROOT}.
Here is the line of the ruleset to illustrate:

.. code-block:: apache

   RewriteRule .* - [E=SFC_ROOT:%{DOCUMENT_ROOT}/t3site]

You are of course free to make the rules as complex as you like.

There might be some files you never want to pull from cache even if they are
indexed. For example you might have some custom speaking url rules that make
your RSS feed accessible as rss.xml. You can skip rewriting to static file with
the following condition:

.. code-block:: apache

   RewriteCond %{REQUEST_FILENAME} !^.*\.xml$

Keep in mind: If you are using the gzip feature of StaticFileCache you have to
take care, that the output is not encoded twice. If the result of the page are
cryptic chars like "�‹��í[krÛH’þ-Eô�ª¹±-¹[ À—�É${dùÙkÙ�[îé..." remove the
"text/html \" in the mod_deflate section of the default TYPO3 .htaccess rules.

Troubleshooting: It is also necessary that the autogenerated .htaccess files in
the StaticFileCache directory (by default *typo3temp/tx_staticfilecache*) will
be not ignored by the server. Sometimes in the vHost configuration various
directories could be marked to deny an overwrite by an additional .htaccess file
for performance reasons.
