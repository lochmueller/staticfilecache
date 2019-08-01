<?php

/**
 * Override original to use our own controller.
 */

declare(strict_types = 1);

namespace SFC\Staticfilecache\ViewHelpers\Be\Widget;

use SFC\Staticfilecache\ViewHelpers\Be\Widget\Controller\PaginateController;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\ResponseInterface;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Fluid\Core\Widget\AbstractWidgetViewHelper;

/**
 * Override original to use our own controller.
 */
class PaginateViewHelper extends AbstractWidgetViewHelper
{
    /**
     * Controller
     *
     * @var \TYPO3\CMS\Fluid\ViewHelpers\Be\Widget\Controller\PaginateController
     */
    protected $controller;

    /**
     * Inject paginate controller
     *
     * @param \TYPO3\CMS\Fluid\ViewHelpers\Be\Widget\Controller\PaginateController $controller
     */
    public function injectPaginateController(\TYPO3\CMS\Fluid\ViewHelpers\Be\Widget\Controller\PaginateController $controller)
    {
        $this->controller = $controller;
    }

    /**
     * Initialize arguments.
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('objects', 'array', 'The QueryResult containing all objects.', true);
        $this->registerArgument('as', 'string', 'as', true);
        $this->registerArgument('configuration', 'array', 'configuration', false, ['itemsPerPage' => 10, 'insertAbove' => false, 'insertBelow' => true, 'maximumNumberOfLinks' => 99]);
    }

    /**
     * Render
     *
     * @return string
     */
    public function render()
    {
        return $this->initiateSubRequest();
    }

    /**
     * Init subrequest.
     *
     * @return ResponseInterface
     */
    protected function initiateSubRequest()
    {
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->controller = $objectManager->get(PaginateController::class);

        return parent::initiateSubRequest();
    }
}
