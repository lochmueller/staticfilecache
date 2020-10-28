<?php

/**
 * AbstractHttpPushTest.
 */

declare(strict_types=1);

namespace SFC\Staticfilecache\Tests\Unit\Service\HttpPush;

use SFC\Staticfilecache\Tests\Unit\AbstractTest;

/**
 * AbstractHttpPushTest.
 */
abstract class AbstractHttpPushTest extends AbstractTest
{
    protected function getExampleContent(): string
    {
        return '<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- external should not be found -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <link rel="stylesheet" href="/bootstrap/4.3.9/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

    <link rel="stylesheet" href="/typo3temp/assets/bootstrappackage/fonts/346739da479e213b7b079a21c35f9ffac6feb37c93b4210969602358a8011f68/webfont.css" />
    <title>Hello, world!</title>

    <style>body:before{user-select:none;pointer-events:none;background-position:center center;background-repeat:no-repeat;content:\'\';position:fixed;top:-100%;left:0;z-index:10000;opacity:0;height:100%;width:100%;background-color:#333333;background-image: url(\'/typo3conf/ext/bootstrap_package/Resources/Public/Images/BootstrapPackageInverted.svg\');background-size:180px 52px;user-select:initial;pointer-events:initial;}.js body:before,.wf-loading body:before{top:0;opacity:1!important;}.wf-active body:before,.wf-inactive body:before{top: 0;opacity:0!important;user-select:none;pointer-events:none;-webkit-transition:opacity 0.25s ease-out;-moz-transition:opacity 0.25s ease-out;-o-transition:opacity 0.25s ease-out;transition:opacity 0.25s ease-out;}</style>
    <script>WebFontConfig={"custom":{"urls":["\/typo3temp\/assets\/bootstrappackage\/fonts\/34b6f09d2160836c09a63c8351093eadf788ed4cb9c6c596239ff2ffe69204f8\/webfont.css","\/typo3conf\/ext\/bootstrap_package\/Resources\/Public\/Fonts\/bootstrappackageicon.min.css"],"families":["Source Sans Pro:300,400,700","BootstrapPackageIcon"]},"timeout":1000};(function(d){var wf=d.createElement(\'script\'),s=d.scripts[0];wf.src=\'/typo3conf/ext/bootstrap_package/Resources/Public/Contrib/webfontloader/webfontloader.js\';wf.async=false;s.parentNode.insertBefore(wf,s);})(document);</script>
  </head>
  <body>
    <h1>Hello, world!</h1>

    <script src="/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>

    <img src="/test.png" />
    <img src="/test1.jpg" />
    <img src="/test2.jpeg" />

    <!-- external should not be found -->
    <script src="https://www.google.de/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>

    <!-- Google should not be found-->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({\'gtm.start\':
      new Date().getTime(),event:\'gtm.js\'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!=\'dataLayer\'?\'&l=\'+l:\'\';j.async=true;j.src=
    \'https://www.googletagmanager.com/gtm.js?id=\'+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,\'script\',\'dataLayer\',\'GTM-XYZ\');</script>
  </body>
</html>';
    }
}
