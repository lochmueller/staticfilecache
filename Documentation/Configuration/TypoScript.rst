.. include:: /Includes.rst.txt

TypoScript
^^^^^^^^^^

You can disable the StaticFileCache by adding one line of TS:

.. code-block:: typoscript

   config.tx_staticfilecache.disableCache = 1

This is also useful, if you want to disable StaticFileCache for specific page
types or other :ref:`conditions <t3tsref:conditions>`, for example:

.. code-block:: typoscript

   [getTSFE().type == <type>]
   config.tx_staticfilecache.disableCache = 1
   [global]
