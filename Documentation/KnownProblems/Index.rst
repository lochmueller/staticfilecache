Known problems
--------------

mnoGoSearch extension uses content post processing hook to modify the content when indexer fetches pages. This is necessary to respect TYPO3SEARCH_xxx markers. Static file cache bypasses the hook and always returns original content. Thus mnogosearch does not work properly.

In order to solve the problem, the following should be added to .htaccess file of the web site:

.. code-block:: bash

   RewriteCond %{HTTP:X-TYPO3-mnogosearch} ^$


If you find any other problems, please report them over at forge: http://forge.typo3.org/projects/extension-nc_staticfilecache/issues

Please take some time and make a proper report stating at least:

- version of the extension
- reproducibility
- steps to reproduce
- observed behavior
- expected behavior

Writing a good bug report will help us to fix the bugs faster and better.