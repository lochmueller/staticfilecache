<?php
/**
 * ManifestService
 */

namespace SFC\Staticfilecache\Service;


/**
 * ManifestService
 */
class ManifestService extends AbstractService
{

    //  header("Content-Type: text/cache-manifest");


#header("Cache-Control: no-cache, must-revalidate");
#header("Expires: ".date(DATE_RFC1123));

// AddType text/cache-manifest .appcache

}
