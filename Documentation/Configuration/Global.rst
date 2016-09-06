Global configuration
^^^^^^^^^^^^^^^^^^^^

The extension has several global configuration options that are accessible through the extension manager.

*Clear cache for all domains in tree*

Here you can decide what to do when clearing the frontend cache. By default all static html files will be deleted. Usually this is fine. Most installations of TYPO3 serve a single domain. If multiple domains are served from the same TYPO3 tree you might want to leave the cache for the other domains intact. If you uncheck the 'clearCacheForAllDomains' checkbox, only the html files are removed that are in the same domain as which you are logged into the backend.

*Send cache control header*

If your apache has mod_expires loaded, you can use it to make nc_staticfilecache send Cache-Control headers together with the statically served files. This is accomplished by writing a .htaccess file for every statically cached file.

*Enable static file compression*

It is now possible to write a gzipped version of the static file to disk. It will be written in addition to the non-gzipped version. If the visitors browser supports gzip, the gzipped static file will be offered along with the proper headers. Apache can also gzip html content on every request, but static file cache gzips the content only once and then caches is. This will free up some precious CPU cycles and time.

*Show generation timestamp signature*

When checking this box, a signature will be added to each generated static html file. This is useful for debugging purposes. This allows you to peak at the source of a page and see if the signature is present. If it is, then the file came from the static html cache.

*Timestamp format*

This allows you to format the time according to your own locale. Please see the php manual for the function 'strftime'.

*Recreate URI*

Recreate URI by typoLink to have a valid cached file name.

*TS: tx_ncstaticfilecache.disableCache*

By setting this to a true value, the current page and page branch will not be cached statically.

