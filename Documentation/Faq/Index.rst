.. include:: /Includes.rst.txt

FAQ
---

What does it mean 'The page is not static cacheable via TypoScriptFrontend'?
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

This is NOT an error of StaticFileCache. The `TSFE` of your instance is set to `no_cache` in the frontend rendering process.
This could be any plugin, content element, TypoScript configuration, or cHash calculation problem. I suggest to check the `TSFE->set_no_cache` function and enabling the TYPO3 syslog to get the real reason why the page is not cached.
Please do not create GitHub issues related to this message and search here on Github or check the Slack channel for many answers.

My page is not cachable and I am using EXT:form incl. Honeypot
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

This is the reason: :ref:`ext_form:faq-honeypt-session`.

Ok, I did everything by the book, but no static pages are being generated. What's up?
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

You are logged into the backend. No pages are cached if you are logged into the backend. This is regular TYPO3 cache behavior. Either log out of the backend (clear your `be_typo_user cookie`!) or use a different browser to generate frontend hits.

How does the .htaccess mod_rewrite ruleset work?
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If all conditions are true, the following rule is applied.
If none of the conditions are met, mod_rewrite falls back to the next ruleset.

Ok, so I logged out of the backend, but I still don't get to see the statically cached pages, what's up?
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Hum ... although you have logged out of the backend, your `be_typo_user` cookie is still in the browser. The cookie is set by default to expire at the end of the browser session. You need to either restart your browser or go to your browser's cookie management tool and drop the cookie manually.

I use 'helhum/typo3-secure-web' and the files are not in the public directory. Any hints?
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Yes. The problem was discussed in the `GitHub issue #180 <https://github.com/lochmueller/staticfilecache/issues/180>`__. A possible solution is described :ref:`here <thirdPartyExtensions>`.
