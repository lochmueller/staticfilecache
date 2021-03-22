<?php

/**
 * ManifestService.
 */

declare(strict_types=1);

namespace SFC\Staticfilecache\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SFC\Staticfilecache\Cache\IdentifierBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * ManifestService.
 *
 * For handling the Offline functions
 */
class ManifestMiddleware implements MiddlewareInterface
{
    /**
     * Generate the manifest file content.
     */
    public function generateManifestContent(string $filename, string &$data): string
    {
        return ''; /* @todo feel free to add parser for JS, CSS and IMAGE
        $content = [
            'CAHCHE MANIFEST',
            '# Created at '.date(\DateTime::COOKIE),
        ];

        $regex = '/(\/[^"\']*\.(?:png|jpg|jpeg|gif|png|svg))/i';
        if (preg_match($regex, $data, $matches)) {
            foreach ($matches as $match) {
                $content[] = $match;
            }
        }

        return implode("\n", $content);*/
    }

    /**
     * Frontend call of the appcache files.
     */
    public function callEid(): void
    {
        header('Content-Type: text/cache-manifest');
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: '.date(DATE_RFC1123));

        try {
            $identifierBuilder = GeneralUtility::makeInstance(IdentifierBuilder::class);
            // $fileName =
            $identifierBuilder->getFilepath(GeneralUtility::getIndpEnv('TYPO3_REQUEST_URL'));

            // var_dump($fileName);
        } catch (\Exception $exception) {
            // $this->lo
            //$exception->getMessage()
        }
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $handler->handle($request);
    }
}
