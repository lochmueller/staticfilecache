.. include:: /Includes.rst.txt

Known problems
==============

Apache 2.4.7 has a bug in the mod_dir-mod_rewrite combination. This is the reason why the homepage (just "/") could not be redirected to the generated static file (all other links should work). More information in this bugreport:
https://bz.apache.org/bugzilla/show_bug.cgi?id=56434

mnoGoSearch extension uses content post processing hook to modify the content when indexer fetches pages. This is necessary to respect TYPO3SEARCH_xxx markers. StaticFileCache bypasses the hook and always returns original content. Thus mnogosearch does not work properly.

In order to solve the problem, the following should be added to .htaccess file of the web site:

.. code-block:: bash

   RewriteCond %{HTTP:X-TYPO3-mnogosearch} ^$


If you find any other problems, please create an according
`GitHub issue <https://github.com/lochmueller/staticfilecache/issues>`__.

Please take some time and make a proper report stating at least:

- version of the extension
- reproducibility
- steps to reproduce
- observed behavior
- expected behavior

Writing a good bug report will help us to fix the bugs faster and better.

*insertPageIncache hook problem*

There are situations, where the page is created with a new URL, but TYPO3 could fetch the page via cache already. In this case the insertPageIncache hook is not called again and the page will not cached staticly.

.. code-block:: typoscript

   config.linkVars = L
   config.defaultGetVars {
       L = 0
   }

In this configuration example you get two pages like :samp:`http://www.domain.org/`
and :samp:`http://www.domain.org/en/` but TYPO3 call the hook only once.

Find more details and a workaround of this problem in this
`comment <https://github.com/lochmueller/staticfilecache/issues/7#issuecomment-317096513>`__
of GitHub issue #7.

Caching Framework with none-DB-backends
---------------------------------------

There are some problems with the caching framework with "non-DB-backends", because the CLI mode set the backends to FileBackend. So it is not possible to run commands as "flush", because the flush command only delete the "CLI file backend cache" and not the e.g. Redis/APCU-Cache.

Please keep this in mind. If you use StaticFileCache you do not need to use a Redis oder APCU cache backend for Pages and Pagesections.

Clear all caches for editors
----------------------------

The possibility to clear all caches is not very useful for regular editor, because they do not understand what exactly means "clear all caches". So it is recommended to use the PageTSConfig option "options.clearCache.pages" to prevent a system wide clear cache mechanism for editors. Ask the editor "why" they use the "clear all cache" and use cache tags, cache groups and clear cache hooks to create the right situation without clearing all caches.

Hard remove of cache_* tables
-----------------------------

There are mechanisms (e.g. typo3_console clear cache force) that truncate the cache_* tables directly. In this case the StaticFileCache is not in sync anymore (the DB and file layer).
If you are using such mechanism, please use the "renameTablesToOtherPrefix" configuration that the caching tables of the StaticFileCache get another prefix.

Using Garbage Collection
------------------------

By using the garbage collection of the core. Please select the right backend.
The Typo3DatabaseBackend should only selected in combination with the
StaticFileCacheBackend to avoid problems in the cache structure.

