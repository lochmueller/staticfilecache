<?php

namespace SFC\Staticfilecache\Service;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * ObjectFactoryService
 */
class ObjectFactoryService extends AbstractService
{
    /**
     * Get the objects for the given category.
     *
     * @param string $category
     * @return array
     */
    public function get(string $category): array
    {
        $objects = [];
        foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['staticfilecache'][$category] ?? [] as $className) {
            $objects[] = GeneralUtility::makeInstance($className);
        }

        return $objects;
    }

    /**
     * Set the given classnames in the category.
     *
     * @param string $category
     * @param array $classNames
     */
    public function set(string $category, array $classNames)
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['staticfilecache'][$category] = $classNames;
    }
}
