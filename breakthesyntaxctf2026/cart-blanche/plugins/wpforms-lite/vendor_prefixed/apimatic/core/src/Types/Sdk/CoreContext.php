<?php

declare (strict_types=1);
namespace WPForms\Vendor\Core\Types\Sdk;

abstract class CoreContext
{
    protected $request;
    protected $response;
    /**
     * Create an instance of HttpContext for a Http Call
     *
     * @param mixed $request  Request first sent on http call
     * @param mixed $response Response received from http call
     */
    public function __construct($request, $response)
    {
        $this->request = $request;
        $this->response = $response;
    }
    /**
     * Returns the HTTP request
     */
    public abstract function getRequest();
    /**
     * Returns the HTTP response
     */
    public abstract function getResponse();
}
