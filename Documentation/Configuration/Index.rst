Configuration
-------------

*NOTE that no static files will be created when you are logged into the backend in the same browser session as you are doing the frontend requests with (unless you Ctrl Shift Reload).*

Some browsers keep the be_typo_user cookie even when you have logged out of the backend. The cookie will only disappear when you completely close the browser session. Some browsers even store session cookies between stopping and restarting a browser. In such a case you will need to remove the cookies by hand and then restart the browser.

I recommend to use two browsers when testing this extension. One for browsing the frontend and one for configuring the extension in TYPO3 (backend).

.. toctree::

	PageConfiguration
	Global
	Htaccess
	Nginx