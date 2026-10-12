.. include:: /Includes.rst.txt

Known problems
==============

Apache 2.4.7 has a bug in the mod_dir-mod_rewrite combination. This is the reason why the homepage (just "/") could not be redirected to the generated static file (all other links should work). More information in this bugreport:
https://bz.apache.org/bugzilla/show_bug.cgi?id=56434

If you find any other problems, please create an according
`GitHub issue <https://github.com/lochmueller/staticfilecache/issues>`__.

Please take some time and make a proper report stating at least:

- version of the extension
- reproducibility
- steps to reproduce
- observed behavior
- expected behavior

Writing a good bug report will help us to fix the bugs faster and better.

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

