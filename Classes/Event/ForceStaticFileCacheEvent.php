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
     * @param bool $forceStatic
     * @param TypoScriptFrontendController $frontendController
     * @param ServerRequestInterface $request
     */
    public function __construct(bool $forceStatic, ?TypoScriptFrontendController $frontendController, ServerRequestInterface $request)
    {
        $this->forceStatic = $forceStatic;
        $this->frontendController = $frontendController;
        $this->request = $request;
    }

    /**
     * @return bool
     */
    public function isForceStatic(): bool
    {
        return $this->forceStatic;
    }

    /**
     * @param bool $forceStatic
     */
    public function setForceStatic(bool $forceStatic): void
    {
        $this->forceStatic = $forceStatic;
    }

    /**
     * @return TypoScriptFrontendController
     */
    public function getFrontendController(): TypoScriptFrontendController
    {
        return $this->frontendController;
    }

    /**
     * @param TypoScriptFrontendController $frontendController
     */
    public function setFrontendController(TypoScriptFrontendController $frontendController): void
    {
        $this->frontendController = $frontendController;
    }

    /**
     * @return ServerRequestInterface
     */
    public function getRequest(): ServerRequestInterface
    {
        return $this->request;
    }

    /**
     * @param ServerRequestInterface $request
     */
    public function setRequest(ServerRequestInterface $request): void
    {
        $this->request = $request;
    }
}
