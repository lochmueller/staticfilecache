<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Service;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class InlineAssetsService.
 *
 * @author Marcus Förster ; https://github.com/xerc
 */
class InlineAssetsService extends AbstractService
{
    public function replaceInlineContent(string $content): string
    {
        /** @var ConfigurationService $configurationService */
        $configurationService = GeneralUtility::makeInstance(ConfigurationService::class);

        foreach ($configurationService->getAll() as $index => $value) {
            if ('inlineService' === substr($index, 0, 13) && (bool) $value) {
                $content = GeneralUtility::makeInstance('SFC\Staticfilecache\Service\InlineAssets\Inline' . substr($index, 13))->replaceInline($content);
            }
        }

        return $content;
    }
}
