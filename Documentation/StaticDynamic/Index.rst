Static dynamic extensions
-------------------------


You can write static-dynamic extensions. These are set to USER, but still display 'dynamic' data (. . . like most visited pages, news, rss feeds etc.). The TYPO3 and static cache will have to be cleared actively as soon as the content of the page changes.

In the case of a news page this may be done using the page TSConfig clearCacheCmd setting for example (http://typo3.org/documentation/document-library/references/doc_core_tsconfig/4.0.0/view/1/3/). You can set the page TSConfig on your news folder so it will automagically clear the cache (and the static page) of your news page.

In the case of a 'random css background' or list of 'most visited' pages this can not be done using the clearCacheCmd. In such a case you can set up the cleaner script. It clears the static files and TYPO3 cache for pages that have been cached and that have 'expired' (static files are older than the cache timeout value).

*Enabling the cleaner script*

There is a cli script that can clear the static files. Run it in your favorite task scheduler. The example uses cron.

Note that you will have to fill in your own values for the php executable and the path to your TYPO3 directory.

Nuskool

A backend user by the name of '_cli_ncstaticfilecache' needs to exist. Create this user before scheduling the task.

.. code-block:: bash

   #minute(0-59) hour(0-23) dayOfMonth(1-31) monthOfYear(1-12) dayOfWeek(0-6)
   #sunday is 0

   # [TYPO3] clear expired static files every hour
   * * * * * /usr/bin/php /var/www/typo3/cli_dispatch.phpsh nc_staticfilecache removeExpiredPages

   # [TYPO3] clear expired static files – quiet mode
   # -s','Silent operation, will only output errors and important messages.
   # --silent','Same as -s
   # -ss','Super silent, will not even output errors or important messages.
   * * * * * /usr/bin/php /var/www/typo3/cli_dispatch.phpsh nc_staticfilecache removeExpiredPages -s

*How often should the cleaner be run?*

Let's say that you have a cached page with an expiry time of an hour after creation and you run the cleaner once every hour. This would mean that if the cleaner has just run at T - 5 the next run will be at T + 3595.

.. code-block:: bash

                    T = 0
                   Cache headers sent: keep this file for 3600 seconds.
                   o--------------------------------------------------->
                   file creation time +3600 seconds
               ^                                                   ^
               |                                                   |
     cleaner check at T – 5                             cleaner check at T + 3595

This means that the cache will not be cleared because it is still 'valid'. The page will expire from the browser cache within 5 seconds. The next cleaner check however would be at T + 7190. By then the page has been expired from the browser cache by 3595 seconds.

So the effect of setting the cleaner script to run at intervals as large as the expiry time is that there are situations where the content is almost TWICE as old as you would want.

If you are fine with content being a maximum of 5% older than wanted age, run your script at an interval of T/20. For a max 1% use T/100, etc.

In cron speak you would get:

for a shortest page expiry time of an hour and a 5% margin you want to check 20 times an hour or every 3 minutes:

.. code-block:: bash

   # [TYPO3] clear expired static files every hour
   */3 * * * * /usr/bin/php /var/www/typo3/cli_dispatch.phpsh nc_staticfilecache removeExpiredPages -s

for a shortest page expiry time of an hour and a 1% margin you want to check 100 times an hour or every 0.6 minutes:

.. code-block:: bash

   # [TYPO3] clear expired static files every hour
   */0.6 * * * * /usr/bin/php /var/www/typo3/cli_dispatch.phpsh nc_staticfilecache removeExpiredPages -s

I would advise you to stick to a lowest value of once every minute though. Unless you know what you are doing. The cleaning operation is very lightweight, but might be taxing for very large sites if run several times a minute.