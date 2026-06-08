<?php

namespace WPForms\Vendor\Core\Logger;

use WPForms\Vendor\CoreInterfaces\Core\Logger\ApiLoggerInterface;
use WPForms\Vendor\CoreInterfaces\Core\Request\RequestInterface;
use WPForms\Vendor\CoreInterfaces\Core\Response\ResponseInterface;
class NullApiLogger implements ApiLoggerInterface
{
    /**
     * @inheritDoc
     */
    public function logRequest(RequestInterface $request) : void
    {
        // noop
    }
    /**
     * @inheritDoc
     */
    public function logResponse(ResponseInterface $response) : void
    {
        // noop
    }
}
