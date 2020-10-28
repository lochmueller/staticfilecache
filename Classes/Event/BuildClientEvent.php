<?php

namespace SFC\Staticfilecache\Event;

class BuildClientEvent
{
    /**
     * @var array
     */
    protected $options;

    /**
     * @var array
     */
    protected $httpOptions;

    public function __construct(array $options, array $httpOptions)
    {
        $this->options = $options;
        $this->httpOptions = $httpOptions;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setOptions(array $options): void
    {
        $this->options = $options;
    }

    public function getHttpOptions(): array
    {
        return $this->httpOptions;
    }

    public function setHttpOptions(array $httpOptions): void
    {
        $this->httpOptions = $httpOptions;
    }
}
