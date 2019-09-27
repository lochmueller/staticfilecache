<?php
/**
 *
 */

declare(strict_types=1);

namespace SFC\Staticfilecache\ViewHelpers\Be\Menus;

class ActionMenuViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Be\Menus\ActionMenuViewHelper
{
    public function render()
    {
        $this->tag->addAttribute('class', 'form-control');
        return parent::render();
    }


}
