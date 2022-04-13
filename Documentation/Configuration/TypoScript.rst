.. include:: /Includes.rst.txt

TypoScript
^^^^^^^^^^

You can disable the StaticFileCache by adding one line of TS:

.. code-block:: typoscript

   config.tx_staticfilecache.disableCache = 1

This is also useful, if you want to disable StaticFileCache for specific page types or other conditions.
https://docs.typo3.org/m/typo3/reference-typoscript/master/en-us/Conditions/Index.html#gettsfe

.. code-block:: typoscript

   [getTSFE().type == YOUR_TYPE_HERE]
   config.tx_staticfilecache.disableCache = 1
   [global]
