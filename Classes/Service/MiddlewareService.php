<?php

/**
 * MiddlewareService
 */
namespace SFC\Staticfilecache\Service;

use Psr\Http\Message\ResponseInterface;

/**
 * MiddlewareService
 */
class MiddlewareService extends AbstractService
{

    /**
     * Current response
     *
     * @var ResponseInterface
     */
    protected static $currentResponse;

    /**
     * Set the current reponse
     *
     * @param ResponseInterface $response
     */
    public static function setResponse(ResponseInterface $response)
    {
        self::$currentResponse = $response;
    }

    /**
     * Get the current response
     *
     * @return ResponseInterface|null
     */
    public static function getResponse()
    {
        return self::$currentResponse;
    }
}
