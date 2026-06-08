<?php

namespace WPForms\Vendor\CoreInterfaces\Sdk;

use WPForms\Vendor\CoreInterfaces\Core\ContextInterface;
use WPForms\Vendor\CoreInterfaces\Core\Request\RequestInterface;
use WPForms\Vendor\CoreInterfaces\Core\Response\ResponseInterface;
interface ConverterInterface
{
    public function createApiException(string $message, RequestInterface $request, ?ResponseInterface $response);
    public function createHttpContext(ContextInterface $context);
    public function createHttpRequest(RequestInterface $request);
    public function createHttpResponse(ResponseInterface $response);
    public function createApiResponse(ContextInterface $context, $deserializedBody);
    public function createFileWrapper(string $realFilePath, ?string $mimeType, ?string $filename);
}
