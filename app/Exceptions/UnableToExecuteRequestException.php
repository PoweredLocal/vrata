<?php

namespace App\Exceptions;
use GuzzleHttp\Psr7\Response;

/**
 * Class UnableToExecuteRequestException
 * @package App\Exceptions
 */
class UnableToExecuteRequestException extends \Exception
{
    /**
     * UnableToExecuteRequestException constructor.
     * @param Response $response
     */
    public function __construct(Response $response = null)
    {
        if ($response) {
            parent::__construct((string)$response->getBody(), $response->getStatusCode());
            return;
        }

        parent::__construct('Unable to finish the request', 502);
    }
}