<?php

/**
 * ObjectFactoryService.
 */

declare(strict_types=1);

namespace SFC\Staticfilecache\Service;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * ObjectFactoryService.
 */
class ObjectFactoryService extends AbstractService
{
    /**
     * Get the objects for the given category.
     */
    public function get(string $category): \Generator
    {
        foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['staticfilecache'][$category] ?? [] as $className) {
            yield GeneralUtility::makeInstance($className);
        }
    }

    /**
     * Set the given classnames in the category.
     */
    public function set(string $category, array $classNames): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['staticfilecache'][$category] = $classNames;
    }
}
