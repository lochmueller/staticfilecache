<?php

/**
 * HtaccessGenerator
 */

declare(strict_types=1);

namespace SFC\Staticfilecache\Cache;

use SFC\Staticfilecache\Service\ConfigurationService;
use SFC\Staticfilecache\Utility\DateTimeUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * HtaccessGenerator
 */
class HtaccessGenerator
{

    /**
     * Configuration
     *
     * @var ConfigurationService
     */
    protected $configuration;

    /**
     * Constructs this backend
     *
     * @param string $context application context
     * @param array $options Configuration options - depends on the actual backend
     */
    public function __construct($context, array $options = [])
    {
        $this->configuration = GeneralUtility::makeInstance(ConfigurationService::class);
    }

    /**
     * Write htaccess file
     *
     * @param string $originalFileName
     * @param int $lifetime
     */
    public function write(string $originalFileName, int $lifetime)
    {
        $sendCCHeader = $this->configuration->getBool('sendCacheControlHeader');
        $sendCCHeaderRedirectAfter = $this->configuration->getBool('sendCacheControlHeaderRedirectAfterCacheTimeout');
        $sendTypo3Headers = $this->configuration->getBool('sendTypo3Headers');
        if (!$sendCCHeader && !$sendCCHeaderRedirectAfter && !$sendTypo3Headers) {
            return;
        }

        $fileName = PathUtility::pathinfo($originalFileName, PATHINFO_DIRNAME) . '/.htaccess';
        $accessTimeout = $this->configuration->get('htaccessTimeout');
        $lifetime = $accessTimeout ? $accessTimeout : $lifetime;

        /** @var StandaloneView $renderer */
        $templateName = 'EXT:staticfilecache/Resources/Private/Templates/Htaccess.html';
        $renderer = GeneralUtility::makeInstance(StandaloneView::class);
        $renderer->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName($templateName));
        $renderer->assignMultiple([
            'mode' => $accessTimeout ? 'A' : 'M',
            'lifetime' => $lifetime,
            'expires' => DateTimeUtility::getCurrentTime() + $lifetime,
            'typo3headers' => $this->getTypoHeaders(),
            'sendCacheControlHeader' => $sendCCHeader,
            'sendCacheControlHeaderRedirectAfterCacheTimeout' => $sendCCHeaderRedirectAfter,
            'sendTypo3Headers' => $sendTypo3Headers,
        ]);

        GeneralUtility::writeFile($fileName, $renderer->render());
    }

    /**
     * Get TYPO3 headers
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
        if (is_array($GLOBALS['TSFE']->config['config']['additionalHeaders.'])) {
            ksort($GLOBALS['TSFE']->config['config']['additionalHeaders.']);
            foreach ($GLOBALS['TSFE']->config['config']['additionalHeaders.'] as $options) {
                $complete = trim($options['header']);
                $parts = explode(':', $complete, 2);
                $headers[trim($parts[0])] = trim($parts[1]);
            }
        }
        return $headers;
    }
}
