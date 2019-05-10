<?php

use SFC\Staticfilecache\Service\ManifestService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

$manifestService = GeneralUtility::makeInstance(ManifestService::class);
$manifestService->callEid();
