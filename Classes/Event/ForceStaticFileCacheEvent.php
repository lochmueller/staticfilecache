<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Event;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

final class ForceStaticFileCacheEvent
{
    private bool $forceStatic;

    private ?TypoScriptFrontendController $frontendController;

    private ServerRequestInterface $request;

    /**
     * ForceStaticFileCacheEvent constructor.
     */
    public function __construct(bool $forceStatic, ?TypoScriptFrontendController $frontendController, ServerRequestInterface $request)
    {
        $this->forceStatic = $forceStatic;
        $this->frontendController = $frontendController;
        $this->request = $request;
    }

    public function isForceStatic(): bool
    {
        return $this->forceStatic;
    }

    public function setForceStatic(bool $forceStatic): void
    {
        $this->forceStatic = $forceStatic;
    }

    public function getFrontendController(): TypoScriptFrontendController
    {
        return $this->frontendController;
    }

    public function setFrontendController(TypoScriptFrontendController $frontendController): void
    {
        $this->frontendController = $frontendController;
    }

    public function getRequest(): ServerRequestInterface
    {
        return $this->request;
    }

    public function setRequest(ServerRequestInterface $request): void
    {
        $this->request = $request;
    }
}
