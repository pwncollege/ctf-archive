<?php

declare (strict_types=1);
namespace WPForms\Vendor\Square\Utils;

use WPForms\Vendor\CoreInterfaces\Core\ContextInterface;
use WPForms\Vendor\CoreInterfaces\Core\Request\RequestInterface;
use WPForms\Vendor\CoreInterfaces\Core\Response\ResponseInterface;
use WPForms\Vendor\CoreInterfaces\Sdk\ConverterInterface;
use WPForms\Vendor\Square\Exceptions\ApiException;
use WPForms\Vendor\Square\Http\ApiResponse;
use WPForms\Vendor\Square\Http\HttpContext;
use WPForms\Vendor\Square\Http\HttpRequest;
use WPForms\Vendor\Square\Http\HttpResponse;
class CompatibilityConverter implements ConverterInterface
{
    public function createApiException(string $message, RequestInterface $request, ?ResponseInterface $response) : ApiException
    {
        $response = $response == null ? null : $this->createHttpResponse($response);
        return new ApiException($message, $this->createHttpRequest($request), $response);
    }
    public function createHttpContext(ContextInterface $context) : HttpContext
    {
        return new HttpContext($this->createHttpRequest($context->getRequest()), $this->createHttpResponse($context->getResponse()));
    }
    public function createHttpRequest(RequestInterface $request) : HttpRequest
    {
        return new HttpRequest($request->getHttpMethod(), $request->getHeaders(), $request->getQueryUrl(), $request->getParameters());
    }
    public function createHttpResponse(ResponseInterface $response) : HttpResponse
    {
        return new HttpResponse($response->getStatusCode(), $response->getHeaders(), $response->getRawBody());
    }
    public function createApiResponse(ContextInterface $context, $deserializedBody) : ApiResponse
    {
        return ApiResponse::createFromContext($context->getResponse()->getBody(), $deserializedBody, $this->createHttpContext($context));
    }
    public function createFileWrapper(string $realFilePath, ?string $mimeType, ?string $filename) : FileWrapper
    {
        return FileWrapper::createFromPath($realFilePath, $mimeType, $filename);
    }
}
