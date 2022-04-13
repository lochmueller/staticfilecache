.. include:: /Includes.rst.txt

Routing
^^^^^^^

Since TYPO3 there is a modern routing mechanims. Please use this to create real/speaking urls.

It is also possible to handle e.g. the XML sitemp. This is the example configuration for a /sitemap.xml incl. sub sitemaps without cHash to get this sitemaps to the StaticFileCache:

.. code-block:: nginx

   routeEnhancers:
     PageTypeSuffix:
       type: PageType
       default: .html
       index: index
       map:
         sitemap.xml: '1533906435'
     SitemapNames:
       type: Simple
       routePath: '/s/{sitemap}'
       requirements:
         sitemap: '.*'
       _arguments:
         sitemap: 'sitemap'
       aspects:
         sitemap:
           type: StaticValueMapper
           map:
             pages: 'pages'
             news: 'news'
             xxxx: 'xxxxx'
     SitemapNamesPage:
       type: Simple
       routePath: '/s/{sitemap}/{page}'
       requirements:
         sitemap: '.*'
         page: '\d+'
       _arguments:
         sitemap: 'sitemap'
         page: page
       aspects:
         sitemap:
           type: StaticValueMapper
           map:
             pages: 'pages'
             news: 'news'
             xxxx: 'xxxxx'
         page:
           type: StaticRangeMapper
           start: '1'
           end: '100'
