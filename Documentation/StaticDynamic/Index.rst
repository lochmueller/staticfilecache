.. include:: /Includes.rst.txt

Static dynamic extensions
-------------------------


You can write static-dynamic extensions. These are set to USER, but still display 'dynamic' data (. . . like most visited pages, news, rss feeds etc.). The TYPO3 and static cache will have to be cleared actively as soon as the content of the page changes.

In the case of a news page this may be done using the page TSConfig :ref:`clearCacheCmd <t3tsconfig:pagetcemain-clearcachecmd>` setting for example. You can set the page TSConfig on your news folder so it will automagically clear the cache (and the static page) of your news page.
