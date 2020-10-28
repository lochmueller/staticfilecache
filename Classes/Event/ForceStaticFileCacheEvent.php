<?php

namespace SFC\Staticfilecache\Event;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class ForceStaticFileCacheEvent
{
    /**
     * @var bool
     */
    protected $forceStatic;

    /**
     * @var TypoScriptFrontendController
     */
    protected $frontendController;

    /**
     * @var ServerRequestInterface
     */
    protected $request;

    /**
     * ForceStaticFileCacheEvent constructor.
     *
     * @param TypoScriptFrontendController $frontendController
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
