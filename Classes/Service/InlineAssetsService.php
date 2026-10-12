<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Service;

use SFC\Staticfilecache\Service\InlineAssets\AbstractInlineAssets;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @author Marcus Förster ; https://github.com/xerc
 */
class InlineAssetsService
{
    public function replaceInlineContent(string $content): string
    {
        /** @var ConfigurationService $configurationService */
        $configurationService = GeneralUtility::makeInstance(ConfigurationService::class);

        foreach ($configurationService->getAll() as $index => $value) {
            if ('inlineService' === substr($index, 0, 13) && (bool) $value) {
                $className = 'SFC\\Staticfilecache\\Service\\InlineAssets\\Inline' . substr($index, 13);
                if (!is_a($className, AbstractInlineAssets::class, true)) {
                    continue;
                }
                $content = GeneralUtility::makeInstance($className)->replaceInline($content);
            }
        }

        return $content;
    }
}
