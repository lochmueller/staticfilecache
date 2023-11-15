<?php
declare(strict_types=1);

namespace SFC\Staticfilecache\ViewHelpers\Format;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class StripEmptyVerticalSpacesViewHelper extends AbstractViewHelper
{
    public function render(): string
    {
        $content = $this->renderChildren();
        return preg_replace('#^\h*\v#ms', '', $content);
    }
}
