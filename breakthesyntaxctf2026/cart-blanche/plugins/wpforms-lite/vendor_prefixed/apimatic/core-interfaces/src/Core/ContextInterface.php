<?php

namespace WPForms\Vendor\CoreInterfaces\Core;

use WPForms\Vendor\CoreInterfaces\Core\Request\RequestInterface;
use WPForms\Vendor\CoreInterfaces\Core\Response\ResponseInterface;
interface ContextInterface
{
    public function getRequest() : RequestInterface;
    public function getResponse() : ResponseInterface;
}
