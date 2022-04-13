.. include:: /Includes.rst.txt

Recommended
^^^^^^^^^^^

- Apache mod_expires
- TYPO3 routing configuration or TYPO3 realurl (current version >= 2.3.1) or any other speaking URL mechanism

You have to create a smart strategy to avoid editors from hitting the clear cache button all the time (more important in Boost-Mode):
I suggest this UserTsConfig:

.. code-block:: typoscript

   options.clearCache.pages = 0
   options.clearCache.all = 0

The user could use the clear cache in the page module or the admin have to use clearCacheCmd in a smart way.

Try to avoid this configuration:

.. code-block:: typoscript

   [backend.user.isLoggedIn]
       config.no_cache = 1
   [global]
