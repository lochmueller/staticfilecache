.. include:: /Includes.rst.txt

Installation
============

-  Download and install the extension with composer (recommended):

   .. code-block:: bash

      composer require lochmueller/staticfilecache

-  Change your .htaccess, vhost or nginx configuration so it supports rewriting to static files according to the templates in https://github.com/lochmueller/staticfilecache/blob/master/Documentation/Configuration.
-  Make sure `typo3temp/tx_staticfilecache` is accessible publicly.
-  Clear the TYPO3 cache.

After installation of the extension, you will need to clear the frontend cache
and the backend cache. The reason for this is that the extension uses the TYPO3
cache hooks to generate static files. So if a page has already been cached by
TYPO3, the hook will not be called and the static file will not be generated.

Verify the functionality
========================

-  Check the statcifilecache configuration test in the backend module.
-  Enable `basic.debugHeaders` in the extension settings.
-  Open some frontend pages in a different browser than the one you're logged in with TYPO3.
-  Check the staticfilecache module - are there cached pages listed? good!
-  Check the your typo3temp/tx_staticfilecache folder for content. Is there content? Good!
-  In your code, make sure there are no INT objects on pages you want to be cached. A single statically-uncacheable element will make the entire page statically-uncacheable.
-  In the second browser, hard reload the pages you previously called and check for x-sfc headers. Loaded? Good! `x-sfc-state: StaticFileCache  via Fallback Middleware?` That is ok, but there's something missing in the .htaccess. `x-sfc-state: TYPO3 - already in cache` Perfect! You're set.


Third Party Extensions
======================

helhum/typo3-secure-web
-----------------------

-  `typo3-secure-web`` uses symlinks to move code out of the docroot. Therefore, it is important to make sure the statically cached pages are accessible for clients to reach. One way is to move the `tx_staticfilecache` into a path that is already covered by `typo3-secure-web` redirects:

.htaccess:

.. code-block:: apache

      # Full path for redirect
      RewriteRule .* - [E=SFC_FULLPATH:typo3temp/assets/tx_staticfilecache/%{ENV:SFC_PROTOCOL}_%{ENV:SFC_HOST}_%{ENV:SFC_PORT}%{ENV:SFC_URI}/index]

      # Do not allow direct call the cache entries
      RewriteCond %{ENV:SFC_URI} ^/typo3temp/assets/tx_staticfilecache/.*


LocalConfiguration.php:

.. code-block:: php

      'overrideCacheDirectory' => '../typo3-secure-web/typo3temp/assets/tx_staticfilecache/',

-  Heads up: the configuration hints in the backend module aren't always accurate with `typo3-secure-web`.


in2code/powermail
-----------------

-  Powermail form plugins are INT, so they disable static caching. Powermail has a global setting `basic.enableCaching`, that you can use, but that will disable php side prefilling functionality in all Powermail forms.
