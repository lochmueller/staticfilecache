<?php

/**
 * HtaccessService.
 */
declare(strict_types = 1);

namespace SFC\Staticfilecache\Service;

use SFC\Staticfilecache\Utility\DateTimeUtility;
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
        $sendCCHeader = $configuration->isBool('sendCacheControlHeader');
        $redirectAfter = $configuration->isBool('sendCacheControlHeaderRedirectAfterCacheTimeout');
        $sendTypo3Headers = $configuration->isBool('sendTypo3Headers');
        if (!$sendCCHeader && !$redirectAfter && !$sendTypo3Headers) {
            return;
        }

        $fileName = PathUtility::pathinfo($originalFileName, PATHINFO_DIRNAME) . '/.htaccess';
        $accessTimeout = $configuration->get('htaccessTimeout');
        $lifetime = $accessTimeout ? $accessTimeout : $lifetime;

        /** @var StandaloneView $renderer */
        $renderer = GeneralUtility::makeInstance(StandaloneView::class);
        $renderer->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName($this->getTemplateName()));
        $renderer->assignMultiple([
            'mode' => $accessTimeout ? 'A' : 'M',
            'lifetime' => $lifetime,
            'expires' => DateTimeUtility::getCurrentTime() + $lifetime,
            'typo3headers' => $this->getTypoHeaders(),
            'sendCacheControlHeader' => $sendCCHeader,
            'sendCacheControlHeaderRedirectAfterCacheTimeout' => $redirectAfter,
            'sendTypo3Headers' => $sendTypo3Headers,
        ]);

        GeneralUtility::writeFile($fileName, $renderer->render());
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
