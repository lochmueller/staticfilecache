<?php

namespace SFC\Staticfilecache\Generator;

use Psr\Http\Message\ResponseInterface;
use SFC\Staticfilecache\Service\ConfigurationService;
use SFC\Staticfilecache\Service\DateTimeService;
use SFC\Staticfilecache\Service\HttpPushService;
use SFC\Staticfilecache\Service\MiddlewareService;
use SFC\Staticfilecache\Service\RemoveService;
use SFC\Staticfilecache\Service\TagService;
use SFC\Staticfilecache\Service\TypoScriptFrontendService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * HtaccessGenerator
 */
class HtaccessGenerator extends AbstractGenerator
{

    /**
     * Generate file.
     *
     * @param string $entryIdentifier
     * @param string $fileName
     * @param string $data
     * @param int $lifetime
     */
    public function generate(string $entryIdentifier, string $fileName, string &$data, int $lifetime): void
    {
        $configuration = GeneralUtility::makeInstance(ConfigurationService::class);
        $tagService = GeneralUtility::makeInstance(TagService::class);

        $htaccessFile = PathUtility::pathinfo($fileName, PATHINFO_DIRNAME) . '/.htaccess';
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
            'httpPushHeaders' => GeneralUtility::makeInstance(HttpPushService::class)->getHttpPushHeaders($data),
        ];

        $this->renderTemplateToFile($this->getTemplateName(), $variables, $htaccessFile);
    }

    /**
     * Remove file.
     *
     * @param string $entryIdentifier
     * @param string $fileName
     */
    public function remove(string $entryIdentifier, string $fileName): void
    {
        $htaccessFile = PathUtility::pathinfo($fileName, PATHINFO_DIRNAME) . '/.htaccess';
        $removeService = GeneralUtility::makeInstance(RemoveService::class);
        $removeService->file($htaccessFile);
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
     * @param string $htaccessFile
     */
    protected function renderTemplateToFile(string $templateName, array $variables, string $htaccessFile)
    {
        /** @var StandaloneView $renderer */
        $renderer = GeneralUtility::makeInstance(StandaloneView::class);
        $renderer->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName($templateName));
        $renderer->assignMultiple($variables);
        $content = \trim((string)$renderer->render());
        // Note: Create even empty htaccess files (do not check!!!), so the delete is in sync
        GeneralUtility::writeFile($htaccessFile, $content);
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
