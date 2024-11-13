.. include:: /Includes.rst.txt

.. _extended:

Extended
========

Events
------

There are several Events to extend the functionality of EXT:staticfilecache. The following list contains all events and a short description of the execution.

- `SFC\Staticfilecache\Event\BuildClientEvent` Executed in the client build process to modify options of the HTTP client for the boost mode queue.
- `SFC\Staticfilecache\Event\BuildIdentifierEvent` Executed in the identifier build process to control the target path of the cache entry.
- `SFC\Staticfilecache\Event\CacheRuleEvent` Executed in the PrepareMiddleware to check if the current page is static cacheable.
- `SFC\Staticfilecache\Event\CacheRuleFallbackEvent` Executed in the FallbackMiddleware to check if the current page is delivered via FallbackMiddleware.
- `SFC\Staticfilecache\Event\ForceStaticFileCacheEvent` Executed in the ForceStaticCacheListener to force the generation of static file cache files.
- `SFC\Staticfilecache\Event\GeneratorConfigManipulationEvent` Executed in the ConfigGenerator do modify the stored data.
- `SFC\Staticfilecache\Event\GeneratorContentManipulationEvent` Executed from all content related Generator to modify the content in front of the storage.
- `SFC\Staticfilecache\Event\GeneratorCreate` Executed to trigger the Generators to store static files.
- `SFC\Staticfilecache\Event\GeneratorRemove` Executed to trigger the Generators to remove static files.
- `SFC\Staticfilecache\Event\HttpPushHeaderEvent` Executed in the HttpPushService to handle different types of push handlers.
- `SFC\Staticfilecache\Event\PreGenerateEvent` Executed in the GenerateMiddleware in front of the business logic of the middleware.

Middleware
----------

The EXT:staticfilecache uses middlewares to implement the logic of the cache into the core. These middlewares are easily to extend/replace. The following list describe the different middlewares:

- `SFC\Staticfilecache\Middleware\CookieCheckMiddleware` Check if there is a frontend cookie and invalid user session. Then remove the cookie.
- `SFC\Staticfilecache\Middleware\FallbackMiddleware` Handle the fallback, if there is a static file but no redirect via nginx/htaccess.
- `SFC\Staticfilecache\Middleware\FrontendCacheMiddleware` Preparation for new caching mechanisms of TYPO3 v13 (currently disabled).
- `SFC\Staticfilecache\Middleware\FrontendUserMiddleware` Handle the user session in the right way.
- `SFC\Staticfilecache\Middleware\GenerateMiddleware` Write the content into the StaticFileCache structure.
- `SFC\Staticfilecache\Middleware\PrepareMiddleware` Check if the content is cacheable in the StaticFileCache structure.

