.. include:: /Includes.rst.txt

FAQ
---

*Q: What means 'The page is not static cacheable via TypoScriptFrontend'*
A: This is NOT a error of StaticFileCache. The TSFE of your instance is set to "no_cache" in the frontend rendering process.
This could be any plugin, content element, TypoScript configuration or cHash calculation problem. I suggest do check the TSFE->set_no_cache function and enable the TYPO3 syslog to get the real reason, why the page is not cached.
Please do not create GitHub issues related to this message and search here on Github or check the Slack channel for many answers.

*Q: My page is not cachable and I am using EXT:form incl. Honeypot?*
A: This is the reason: ":ref:`ext_form:faq-honeypt-session`".

*Q: Tell me about caching*
A: Here's a nice writeup on caching I found: "`Port80 Software has sunset its line of top-tier IIS server security products <https://www.port80software.com/products/cacheright/cachingandcachecontrol>`__".

*Q: Ok, I did everything by the book, but no static pages are being generated, what's up?*
A: You are logged into the backend, no pages are cached if you are logged into the backend, just like normal cache. Either logout of the backend (clear your be_typo_user cookie!) or use a different browser to generate frontend hits.

*Q: How does the .htaccess mod_rewrite ruleset work?*
A: The conditions are regex checks. If they are all met, then the rule following them is applied.
If none of the conditions are met, mod_rewrite will fall through to the next ruleset.

*Q: Ok, so I logged out of the backend, but I still don't get to see the statically cached pages, what's up?*
A: Hum ... although you have logged out of the backend, your be_typo_user is still in the browser. The cookie is set by default to expire at the end of the browser session. You need to either restart your browser or go to your browsers cookie management tool and drop the cookie manually.

*Q: I use 'helhum/typo3-secure-web' and the files are not in the public directory. Any hints?*
A: Yes. The problem was discussed in the `GitHub issue #180 <https://github.com/lochmueller/staticfilecache/issues/180>`__ - Easies way is, to set the caching directory via extension configuration to "../private/typo3temp/tx_staticfilecache/".
