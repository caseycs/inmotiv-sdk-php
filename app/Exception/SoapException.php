<?php
declare(strict_types=1);

namespace InMotivClient\Exception;

class SoapException extends InMotivException
{
    public function __construct(string $url, string $method, \Throwable $prev)
    {
        $this->url = $url;
        $this->method = $method;

        parent::__construct(sprintf('InMotiv SOAP call fail, url %s method %s', $url, $method), 0, $prev);
    }
}
