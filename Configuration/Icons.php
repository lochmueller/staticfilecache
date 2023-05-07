<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Utility\GeneralUtility;

return [
    'module-staticfilecache' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:staticfilecache/Resources/Public/Icons/Extension.svg',
    ],
    'brand-amazon' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:staticfilecache/Resources/Public/Icons/BrandAmazon.svg',
    ],
    'brand-paypal' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:staticfilecache/Resources/Public/Icons/BrandPaypal.svg',
    ],
    'documentation-book' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:staticfilecache/Resources/Public/Icons/DocumentationBook.svg',
    ],
];
