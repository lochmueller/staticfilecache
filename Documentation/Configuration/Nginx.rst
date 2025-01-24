.. include:: /Includes.rst.txt

Nginx configuration
^^^^^^^^^^^^^^^^^^^

*Configuration file changes*

This configuration example adds a named location_ called @sfc which replaces the usual invocation of TYPO3's index.php.
The @sfc location_ includes all checks necessary to decide whether the current request can be handled with
a static file or needs to be directed to TYPO3.
If all checks pass, the try_files_ directive is used to find the files in
typo3temp/assets/tx_staticfilecache/ and if unavailable, redirect to index.php.

In your nginx configuration you need to replace your '/' location, which probably looks like the following:

.. code-block:: nginx

   location / {
       try_files $uri $uri/ /index.php$is_args$args;
   }

By the following configuration:

.. code-block:: nginx

   location / {
       try_files $uri $uri/ @sfc;
   }

   # Special root site case. prevent "try_files $uri/" + "index" from skipping the cache
   # by accessing /index.php directly
   location =/ {
       recursive_error_pages on;
       error_page 405 = @sfc;
       return 405;
   }

   location @t3frontend {
       # Using try_files for ease of configuration demonstration here,
       # you can also fastcgi_pass directly to php here
       try_files $uri /index.php$is_args$args;
   }

   location @sfc {
       # Perform an internal redirect to TYPO3 if any of the required
       # conditions for StaticFileCache don't match
       error_page 405 = @t3frontend;

       # Query String needs to be empty
       if ($args != '') {
           return 405;
       }

       # We can't serve static files for logged-in BE/FE users
       if ($cookie_staticfilecache = 'typo_user_logged_in') {
           return 405;
       }

       if ($cookie_be_typo_user != '') {
           return 405;
       }

       # Ensure we redirect to TYPO3 for non GET/HEAD requests
       if ($request_method !~ ^(GET|HEAD)$ ) {
           return 405;
       }

       # Disable cache for EXT:solr indexing requests
       if ($http_x_tx_solr_iq) {
           return 405;
       }

       # Disable cache for EXT:crawler indexing requests
       if ($http_x_t3crawler) {
           return 405;
       }

       charset utf-8;
       default_type text/html;
       try_files /typo3temp/assets/tx_staticfilecache/${scheme}_${host}_${server_port}${uri}/index
             /typo3temp/assets/tx_staticfilecache/${scheme}_${host}_${server_port}${uri}
             =405;
   }

   location /typo3temp/assets/tx_staticfilecache {
       deny all;
   }

If you activate the php generator you need to use this block accepting urls with trailing slashes only

.. code-block:: nginx

        if (!-f $document_root/typo3temp/assets/tx_staticfilecache/${scheme}_${host}_${server_port}${uri}index.php) {
            return 405;
        }

        include /etc/nginx/fastcgi_params;
        fastcgi_pass unix:/var/run/php/php-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root/typo3temp/assets/tx_staticfilecache/${scheme}_${host}_${server_port}${uri}/index.php;

or this block accepting urls with or without trailing slashes

.. code-block:: nginx

        if (!-f $document_root/typo3temp/assets/tx_staticfilecache/${scheme}_${host}_${server_port}${uri}/index.php) {
            return 405;
        }

        include /etc/nginx/fastcgi_params;
        fastcgi_pass unix:/var/run/php/php-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root/typo3temp/assets/tx_staticfilecache/${scheme}_${host}_${server_port}${uri}/index.php;

instead of

.. code-block:: nginx

       charset utf-8;
       default_type text/html;
       try_files /typo3temp/assets/tx_staticfilecache/${scheme}_${host}_${server_port}${uri}/index
             /typo3temp/assets/tx_staticfilecache/${scheme}_${host}_${server_port}${uri}
             =405;

*Extension configuration*

Nginx does not support .htaccess files and therefore cannot transfer HTML headers
for the statically cached files. You should therefore activate the PHP generator,
so that the static content is written to a PHP file via which the correct HTML
headers are set.

.. _location: http://nginx.org/en/docs/http/ngx_http_core_module.html#location
.. _try_files: http://nginx.org/en/docs/http/ngx_http_core_module.html#try_files
