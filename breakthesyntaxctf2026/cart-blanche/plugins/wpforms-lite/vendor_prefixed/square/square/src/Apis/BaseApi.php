<?php

declare (strict_types=1);
namespace WPForms\Vendor\Square\Apis;

use WPForms\Vendor\Core\ApiCall;
use WPForms\Vendor\Core\Client;
use WPForms\Vendor\Core\Request\RequestBuilder;
use WPForms\Vendor\Core\Response\ResponseHandler;
/**
 * Base controller
 */
class BaseApi
{
    /**
     * Client instance
     *
     * @var Client
     */
    private $client;
    public function __construct(Client $client)
    {
        $this->client = $client;
    }
    protected function execute(RequestBuilder $requestBuilder, ?ResponseHandler $responseHandler = null)
    {
        return (new ApiCall($this->client))->requestBuilder($requestBuilder)->responseHandler($responseHandler ?? $this->responseHandler())->execute();
    }
    protected function requestBuilder(string $requestMethod, string $path) : RequestBuilder
    {
        return new RequestBuilder($requestMethod, $path);
    }
    protected function responseHandler() : ResponseHandler
    {
        return $this->client->getGlobalResponseHandler();
    }
}
