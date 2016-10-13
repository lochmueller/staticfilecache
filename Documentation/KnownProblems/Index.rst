Known problems
--------------

Apache 2.4.7 has a bug in the mod_dir-mod_rewrite combination. This is the reason why the homepage (just "/") could not be redirected to the generated static file (all other links should work). More information in this bugreport:
https://bz.apache.org/bugzilla/show_bug.cgi?id=56434

mnoGoSearch extension uses content post processing hook to modify the content when indexer fetches pages. This is necessary to respect TYPO3SEARCH_xxx markers. Static file cache bypasses the hook and always returns original content. Thus mnogosearch does not work properly.

In order to solve the problem, the following should be added to .htaccess file of the web site:

.. code-block:: bash

   RewriteCond %{HTTP:X-TYPO3-mnogosearch} ^$


If you find any other problems, please report them over at forge: http://forge.typo3.org/projects/extension-staticfilecache/issues

Please take some time and make a proper report stating at least:

- version of the extension
- reproducibility
- steps to reproduce
- observed behavior
- expected behavior

Writing a good bug report will help us to fix the bugs faster and better.
