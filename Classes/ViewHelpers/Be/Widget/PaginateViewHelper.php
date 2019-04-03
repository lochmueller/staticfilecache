<?php

declare(strict_types = 1);

namespace SFC\Staticfilecache\ViewHelpers\Be\Widget;

use SFC\Staticfilecache\ViewHelpers\Be\Widget\Controller\PaginateController;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Override original to use our own controller.
 */
class PaginateViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Be\Widget\PaginateViewHelper
{
    /**
     * @return \TYPO3\CMS\Extbase\Mvc\ResponseInterface
     */
    protected function initiateSubRequest()
    {
        $objectManager = new ObjectManager();
        $this->controller = $objectManager->get(PaginateController::class);

        return parent::initiateSubRequest();
    }
}
