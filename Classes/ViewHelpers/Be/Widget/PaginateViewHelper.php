<?php

/**
 * Override original to use our own controller.
 */

declare(strict_types=1);

namespace SFC\Staticfilecache\ViewHelpers\Be\Widget;

use SFC\Staticfilecache\ViewHelpers\Be\Widget\Controller\PaginateController;

/**
 * Override original to use our own controller.
 */
class PaginateViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Be\Widget\PaginateViewHelper
{

    /**
     * @var PaginateController
     */
    protected $controllerOverride;

    /**
     * @param PaginateController $controllerOverride
     */
    public function injectPaginateControllerOverride(PaginateController $controllerOverride)
    {
        $this->controllerOverride = $controllerOverride;
    }

    /**
     * Initialize arguments.
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->overrideArgument('objects', 'array', 'The QueryResult containing all objects.', true);
    }

    protected function initiateSubRequest()
    {
        $this->controller = $this->controllerOverride;
        return parent::initiateSubRequest();
    }
}
