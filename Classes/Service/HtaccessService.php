<?php

/**
 * HtaccessService.
 */

declare(strict_types = 1);

namespace SFC\Staticfilecache\Service;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

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
     */
    public function write(string $originalFileName, int $lifetime)
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
            'expires' => (new DateTimeService())->getCurrentTime() + $lifetime,
            'typo3headers' => $this->getTypoHeaders(),
            'sendCacheControlHeader' => $configuration->isBool('sendCacheControlHeader'),
            'sendCacheControlHeaderRedirectAfterCacheTimeout' => $configuration->isBool('sendCacheControlHeaderRedirectAfterCacheTimeout'),
            'sendTypo3Headers' => $configuration->isBool('sendTypo3Headers'),
            'tags' => \implode(',', $tags),
            'tagHeaderName' => $tagService->getHeaderName(),
            'sendStaticFileCacheHeader' => $configuration->isBool('sendStaticFileCacheHeader'),
            'httpPushHeaders' => GeneralUtility::makeInstance(HttpPushService::class)->getHttpPushHeaders(GeneralUtility::getUrl($originalFileName)),
        ];

        $this->renderTemplateToFile($this->getTemplateName(), $variables, $fileName);
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

    /**
     * Get TYPO3 headers.
     *
     * @return array
     */
    protected function getTypoHeaders(): array
    {
        $headers = [];
        if (!($GLOBALS['TSFE'] instanceof TypoScriptFrontendController)) {
            return $headers;
        }
        // Set headers, if any
        if (\is_array($GLOBALS['TSFE']->config['config']['additionalHeaders.'])) {
            \ksort($GLOBALS['TSFE']->config['config']['additionalHeaders.']);
            foreach ($GLOBALS['TSFE']->config['config']['additionalHeaders.'] as $options) {
                $complete = \trim($options['header']);
                $parts = \explode(':', $complete, 2);
                $headers[\trim($parts[0])] = \trim($parts[1]);
            }
        }

        return $headers;
    }
}
