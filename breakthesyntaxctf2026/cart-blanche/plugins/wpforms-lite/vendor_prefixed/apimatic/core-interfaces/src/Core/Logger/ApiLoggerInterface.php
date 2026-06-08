<?php

namespace WPForms\Vendor\CoreInterfaces\Core\Logger;

use WPForms\Vendor\CoreInterfaces\Core\Request\RequestInterface;
use WPForms\Vendor\CoreInterfaces\Core\Response\ResponseInterface;
interface ApiLoggerInterface
{
    /**
     * Log the provided request.
     *
     * @param $request RequestInterface HTTP requests to be logged.
     */
    public function logRequest(RequestInterface $request) : void;
    /**
     * Log the provided response.
     *
     * @param $response ResponseInterface HTTP responses to be logged.
     */
    public function logResponse(ResponseInterface $response) : void;
}
