<?php

namespace WPForms\Vendor\CoreInterfaces\Core\Response;

use WPForms\Vendor\CoreInterfaces\Sdk\ConverterInterface;
interface ResponseInterface
{
    public function getStatusCode() : int;
    /**
     * @return array<string,mixed>
     */
    public function getHeaders() : array;
    public function getRawBody() : string;
    public function getBody();
    public function convert(ConverterInterface $converter);
}
