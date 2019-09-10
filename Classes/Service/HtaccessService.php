<?php

/**
 * HtaccessService.
 */

declare(strict_types = 1);

namespace SFC\Staticfilecache\Service;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * HtaccessService.
 */
class HtaccessService extends AbstractService
{
    /**
     * Write htaccess file.
     *
     * @param string $originalFileName
     * @param int    $lifetime
     * @param string $originalContent
     */
    public function write(string $originalFileName, int $lifetime, string $originalContent)
    {
        $configuration = GeneralUtility::makeInstance(ConfigurationService::class);
        $tagService = GeneralUtility::makeInstance(TagService::class);

        $fileName = PathUtility::pathinfo($originalFileName, PATHINFO_DIRNAME) . '/.htaccess';
        $accessTimeout = (int)$configuration->get('htaccessTimeout');
        $lifetime = $accessTimeout ? $accessTimeout : $lifetime;

        $tags = $tagService->isEnable() ? $tagService->getTags() : [];
        $variables = [
            'mode' => $accessTimeout ? 'A' : 'M',
            'lifetime' => $lifetime,
            'responseHeaders' => $this->getReponseHeaders(),
            'expires' => (new DateTimeService())->getCurrentTime() + $lifetime,
            'typo3headers' => GeneralUtility::makeInstance(TypoScriptFrontendService::class)->getAdditionalHeaders(),
            'sendCacheControlHeader' => $configuration->isBool('sendCacheControlHeader'),
            'sendCacheControlHeaderRedirectAfterCacheTimeout' => $configuration->isBool('sendCacheControlHeaderRedirectAfterCacheTimeout'),
            'sendTypo3Headers' => $configuration->isBool('sendTypo3Headers'),
            'tags' => \implode(',', $tags),
            'tagHeaderName' => $tagService->getHeaderName(),
            'sendStaticFileCacheHeader' => $configuration->isBool('sendStaticFileCacheHeader'),
            'httpPushHeaders' => GeneralUtility::makeInstance(HttpPushService::class)->getHttpPushHeaders($originalContent),
        ];

        $this->renderTemplateToFile($this->getTemplateName(), $variables, $fileName);
    }

    /**
     * Get reponse headers
     *
     * @return array
     */
    protected function getReponseHeaders(): array
    {
        $response = MiddlewareService::getResponse();
        if (!($response instanceof ResponseInterface)) {
            return [];
        }

        $validHeaders = ['Content-Type', 'Content-Language'];
        $headers = $response->getHeaders();
        $result = [];
        foreach ($headers as $name => $values) {
            if (in_array($name, $validHeaders)) {
                $result[$name] =  implode('', $values);
            }
        }

        return $result;
    }

    /**
     * Render template to file.
     *
     * @param string $templateName
     * @param array  $variables
     * @param string $fileName
     */
    protected function renderTemplateToFile(string $templateName, array $variables, string $fileName)
    {
        /** @var StandaloneView $renderer */
        $renderer = GeneralUtility::makeInstance(StandaloneView::class);
        $renderer->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName($templateName));
        $renderer->assignMultiple($variables);
        $content = \trim((string)$renderer->render());
        // Note: Create even empty htaccess files (do not check!!!), so the delete is in sync
        GeneralUtility::writeFile($fileName, $content);
    }

    /**
     * Get the template name.
     *
     * @return string
     */
    protected function getTemplateName(): string
    {
        $configuration = GeneralUtility::makeInstance(ConfigurationService::class);
        $templateName = \trim((string)$configuration->get('htaccessTemplateName'));
        if ('' === $templateName) {
            return 'EXT:staticfilecache/Resources/Private/Templates/Htaccess.html';
        }

        return $templateName;
    }
}
