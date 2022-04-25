<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Service\InlineAssets;

/**
 * Class AbstractInlineFiles.
 */
abstract class AbstractInlineAssets extends SFC\Staticfilecache\Service\HttpPush\AbstractHttpPush
{
    public function __construct()
    {
      /** @var ConfigurationService $configurationService */
      $this->configurationService = GeneralUtility::makeInstance(\SFC\Staticfilecache\Service\ConfigurationService::class);// CHECK ; src-location?!
    }

    protected function includeAssets(string $regex, string $content) {

        return preg_replace_callback($regex, function (array $match): string {
            return $this->parseAsset($match);
        }, $content);
    }

    protected function parseAsset(array $match) {

        $path = $this->streamlineFilePaths((array) $match['src'])[0];
        if(!file_exists($path)) {
            return $match[0];
        }
        $file = file_get_contents($path);
        if(empty($file)) {// TODO ; needet?!
            return $match[0];
        }
        switch($match['ext'][0]) {
          case 'svg':// TODO ; https://github.com/peteboere/css-crush/commit/7cd5d73f67212dfc7ec0f85e4a84932a32ce95d8
              $type = 'svg+xml;utf8';
              $file = str_replace('"','\'',$file);// quotes

              $file = preg_replace('/\s+/',' ',$file);// whitespace
              $file = preg_replace('/#([a-f0-9]{3,6})/','%23$1',$file);// MOD:color
          break;
          case 'ico':
              $type = 'image/x-icon;base64';
              $file = base64_encode($file);
          break;
          case 'woff':
          case 'woff2':
              $type = 'font/'.$match['ext'][0].';base64';
              $file = base64_encode($file);
          default:
              $type = 'image/'.$match['ext'][0].';base64';
              $file = base64_encode($file);
          break;
        }
        return "data:$type,$file";
    }
}
