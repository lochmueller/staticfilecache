The need for speed
------------------

Here are some test results for a USER page generated from cache by TYPO3 and one pushed from static html. The test tool used is apache bench. The “-c” means that 10 simultaneous users are simulated, “-n 500” means that a thousand unique requests are generated.

By default apachebench uses NoKeepAlive which means that every image and css file will use a new apache connection.

Future test may include measurements done sucking a complete site (static + dynamic pages) once with static cache enabled and once without static cache enabled. Sucking tools:

- crawler extension (TYPO3)
- curl
- httrack
- wget
- siege
- ... need to check

Tests were run on a Unibody MacBook Pro with 4GB of ram and a tweaked out apache and mysql configuration.

*The USER cached page*

.. code-block:: bash

   ab -c 10 -n 100 http://some.fictive.domain.org/gnu-gpl-short/

   This is ApacheBench, Version 2.3 <$Revision: 655654 $>
   Copyright 1996 Adam Twiss, Zeus Technology Ltd, http://www.zeustech.net/
   Licensed to The Apache Software Foundation, http://www.apache.org/

   Benchmarking some.fictive.domain.org (be patient)
   Completed 100 requests
   Completed 200 requests
   Completed 300 requests
   Completed 400 requests
   Completed 500 requests
   Finished 500 requests


   Server Software:        Apache
   Server Hostname:        some.fictive.domain.org
   Server Port:            80

   Document Path:          /gnu-gpl-short/
   Document Length:        12088 bytes

   Concurrency Level:      10
   Time taken for tests:   48.854 seconds
   Complete requests:      500
   Failed requests:        28
      (Connect: 0, Receive: 0, Length: 28, Exceptions: 0)
   Write errors:           0
   Total transferred:      6240528 bytes
   HTML transferred:       6044028 bytes
   Requests per second:    10.23 [#/sec] (mean)
   Time per request:       977.077 [ms] (mean)
   Time per request:       97.708 [ms] (mean, across all concurrent requests)
   Transfer rate:          124.74 [Kbytes/sec] received

   Connection Times (ms)
                 min  mean[+/-sd] median   max
   Connect:       12   25  15.5     19     117
   Processing:   492  945 182.0    924    1624
   Waiting:      462  903 174.3    874    1546
   Total:        520  970 184.1    944    1673

   Percentage of the requests served within a certain time (ms)
     50%    944
     66%   1021
     75%   1071
     80%   1104
     90%   1217
     95%   1314
     98%   1414
     99%   1553
     100%   1673 (longest request)

*The static html page pushed by mod_rewrite*

.. code-block:: bash

   ab -c 100 -n 1000 http://some.fictive.domain.org/gnu-gpl-short/

   This is ApacheBench, Version 2.3 <$Revision: 655654 $>
   Copyright 1996 Adam Twiss, Zeus Technology Ltd, http://www.zeustech.net/
   Licensed to The Apache Software Foundation, http://www.apache.org/

   Benchmarking some.fictive.domain.org (be patient)
   Completed 100 requests
   Completed 200 requests
   Completed 300 requests
   Completed 400 requests
   Completed 500 requests
   Finished 500 requests


   Server Software:        Apache
   Server Hostname:        some.fictive.domain.org
   Server Port:            80

   Document Path:          /gnu-gpl-short/
   Document Length:        12038 bytes

   Concurrency Level:      10
   Time taken for tests:   3.697 seconds
   Complete requests:      500
   Failed requests:        0
   Write errors:           0
   Total transferred:      6194000 bytes
   HTML transferred:       6019000 bytes
   Requests per second:    135.24 [#/sec] (mean)
   Time per request:       73.945 [ms] (mean)
   Time per request:       7.394 [ms] (mean, across all concurrent requests)
   Transfer rate:          1636.04 [Kbytes/sec] received

   Connection Times (ms)
                 min  mean[+/-sd] median   max
   Connect:       12   20   6.8     19      83
   Processing:    38   53  17.3     49     149
   Waiting:       19   30  13.8     28     126
   Total:         55   73  18.4     69     168

   Percentage of the requests served within a certain time (ms)
     50%     69
     66%     72
     75%     74
     80%     75
     90%     83
     95%    116
     98%    157
     99%    163
    100%    168 (longest request)

*Test result*

Quick calculation show us a performance increase factor of:
135.24 / 10.23 = 13.22
Let's see that again, but with more energy:
*1322 %*

Wow!

That figure used to read 23000%. But this test was done on a server running on a cluster and not running a PHP accelerator. You may have even more performance increase on a machine running in 'bare metal' mode and running a PHP opcode cache.

Here is an example of my own server running Nginx and APC. It's a lightweight XEN box with 720MB of ram and 4 cores:

*The USER cached page served by Nginx*

.. code-block:: bash

   This is ApacheBench, Version 2.3 <$Revision: 655654 $>
   Copyright 1996 Adam Twiss, Zeus Technology Ltd, http://www.zeustech.net/
   Licensed to The Apache Software Foundation, http://www.apache.org/

   Benchmarking www.typofree.org (be patient)
   Completed 100 requests
   Completed 200 requests
   Completed 300 requests
   Completed 400 requests
   Completed 500 requests
   Completed 600 requests
   Completed 700 requests
   Completed 800 requests
   Completed 900 requests
   Completed 1000 requests
   Finished 1000 requests


   Server Software:        nginx
   Server Hostname:        www.typofree.org
   Server Port:            80

   Document Path:          /articles/optimizing-typo3-backend-responsiveness/
   Document Length:        63637 bytes

   Concurrency Level:      100
   Time taken for tests:   12.341 seconds
   Complete requests:      1000
   Failed requests:        24
      (Connect: 0, Receive: 0, Length: 24, Exceptions: 0)
   Write errors:           0
   Total transferred:      64233296 bytes
   HTML transferred:       63836716 bytes
   Requests per second:    81.03 [#/sec] (mean)
   Time per request:       1234.098 [ms] (mean)
   Time per request:       12.341 [ms] (mean, across all concurrent requests)
   Transfer rate:          5082.89 [Kbytes/sec] received

   Connection Times (ms)
                 min  mean[+/-sd] median   max
   Connect:       27  104 403.0     47    3620
   Processing:   387 1088 372.9   1015    2776
   Waiting:       82  545 324.5    516    1553
   Total:        422 1192 573.9   1061    5080

   Percentage of the requests served within a certain time (ms)
     50%   1061
     66%   1139
     75%   1234
     80%   1310
     90%   1898
     95%   2030
     98%   2691
     99%   4376
    100%   5080 (longest request)

*The static html page pushed by Nginx rewrite*

.. code-block:: bash

   This is ApacheBench, Version 2.3 <$Revision: 655654 $>
   Copyright 1996 Adam Twiss, Zeus Technology Ltd, http://www.zeustech.net/
   Licensed to The Apache Software Foundation, http://www.apache.org/

   Benchmarking www.typofree.org (be patient)
   Completed 100 requests
   Completed 200 requests
   Completed 300 requests
   Completed 400 requests
   Completed 500 requests
   Completed 600 requests
   Completed 700 requests
   Completed 800 requests
   Completed 900 requests
   Completed 1000 requests
   Finished 1000 requests


   Server Software:        nginx
   Server Hostname:        www.typofree.org
   Server Port:            80

   Document Path:          /articles/optimizing-typo3-backend-responsiveness/
   Document Length:        63588 bytes

   Concurrency Level:      100
   Time taken for tests:   11.679 seconds
   Complete requests:      1000
   Failed requests:        0
   Write errors:           0
   Total transferred:      64305368 bytes
   HTML transferred:       64006704 bytes
   Requests per second:    85.63 [#/sec] (mean)
   Time per request:       1167.861 [ms] (mean)
   Time per request:       11.679 [ms] (mean, across all concurrent requests)
   Transfer rate:          5377.20 [Kbytes/sec] received

   Connection Times (ms)
                 min  mean[+/-sd] median   max
   Connect:       30  191 592.3     58    4191
   Processing:   330  920 350.0    837    3245
   Waiting:       30   72  68.2     61    1929
   Total:        494 1111 689.3    917    5254

   Percentage of the requests served within a certain time (ms)
     50%    917
     66%   1106
     75%   1232
     80%   1324
     90%   1553
     95%   2216
     98%   3821
     99%   4270
    100%   5254 (longest request)

That's a slight improvement. But altogehter Nginx can take on much more of a beating than Apache. The CPU rises to a load of 0.4 when taking this beating without static file cache and it idles at 0.0 when static file cache is enabled.

Soo ... bring on those success reports!