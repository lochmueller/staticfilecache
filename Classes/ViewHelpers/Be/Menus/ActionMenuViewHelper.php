<?php

/**
 * ActionMenuViewHelper.
 */

declare(strict_types=1);

namespace SFC\Staticfilecache\ViewHelpers\Be\Menus;

/**
 * ActionMenuViewHelper.
 */
class ActionMenuViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Be\Menus\ActionMenuViewHelper
{
    /**
     * @return string
     */
    public function render()
    {
        $this->tag->addAttribute('class', 'form-control');

        return parent::render();
    }
}
