Installation
------------

- Install the extension in the extension manager
- Change your .htaccess, vhost or NginX configuration so it supports rewriting to static files.
- Clear the TYPO3 cache. After installation of the extension, you will need to clear the frontend cache and the backend cache. The reason for this is that the extension uses the TYPO3 cache hooks to generate static files. So if a page has already been cached by TYPO3, the hook will not be called and the static file will not be generated.