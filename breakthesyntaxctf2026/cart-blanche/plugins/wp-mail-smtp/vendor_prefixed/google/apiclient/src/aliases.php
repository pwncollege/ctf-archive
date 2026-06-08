<?php

namespace WPMailSMTP\Vendor;

if (\class_exists('WPMailSMTP\\Vendor\\Google_Client', \false)) {
    // Prevent error with preloading in PHP 7.4
    // @see https://github.com/googleapis/google-api-php-client/issues/1976
    return;
}
$classMap = ['WPMailSMTP\\Vendor\\Google\\Client' => 'Google_Client', 'WPMailSMTP\\Vendor\\Google\\Service' => 'Google_Service', 'WPMailSMTP\\Vendor\\Google\\AccessToken\\Revoke' => 'Google_AccessToken_Revoke', 'WPMailSMTP\\Vendor\\Google\\AccessToken\\Verify' => 'Google_AccessToken_Verify', 'WPMailSMTP\\Vendor\\Google\\Model' => 'Google_Model', 'WPMailSMTP\\Vendor\\Google\\Utils\\UriTemplate' => 'Google_Utils_UriTemplate', 'WPMailSMTP\\Vendor\\Google\\AuthHandler\\Guzzle6AuthHandler' => 'Google_AuthHandler_Guzzle6AuthHandler', 'WPMailSMTP\\Vendor\\Google\\AuthHandler\\Guzzle7AuthHandler' => 'Google_AuthHandler_Guzzle7AuthHandler', 'WPMailSMTP\\Vendor\\Google\\AuthHandler\\AuthHandlerFactory' => 'Google_AuthHandler_AuthHandlerFactory', 'WPMailSMTP\\Vendor\\Google\\Http\\Batch' => 'Google_Http_Batch', 'WPMailSMTP\\Vendor\\Google\\Http\\MediaFileUpload' => 'Google_Http_MediaFileUpload', 'WPMailSMTP\\Vendor\\Google\\Http\\REST' => 'Google_Http_REST', 'WPMailSMTP\\Vendor\\Google\\Task\\Retryable' => 'Google_Task_Retryable', 'WPMailSMTP\\Vendor\\Google\\Task\\Exception' => 'Google_Task_Exception', 'WPMailSMTP\\Vendor\\Google\\Task\\Runner' => 'Google_Task_Runner', 'WPMailSMTP\\Vendor\\Google\\Collection' => 'Google_Collection', 'WPMailSMTP\\Vendor\\Google\\Service\\Exception' => 'Google_Service_Exception', 'WPMailSMTP\\Vendor\\Google\\Service\\Resource' => 'Google_Service_Resource', 'WPMailSMTP\\Vendor\\Google\\Exception' => 'Google_Exception'];
foreach ($classMap as $class => $alias) {
    \class_alias($class, 'WPMailSMTP\\Vendor\\' . $alias);
}
/**
 * This class needs to be defined explicitly as scripts must be recognized by
 * the autoloader.
 */
class Google_Task_Composer extends \WPMailSMTP\Vendor\Google\Task\Composer
{
}
/** @phpstan-ignore-next-line */
if (\false) {
    class Google_AccessToken_Revoke extends \WPMailSMTP\Vendor\Google\AccessToken\Revoke
    {
    }
    class Google_AccessToken_Verify extends \WPMailSMTP\Vendor\Google\AccessToken\Verify
    {
    }
    class Google_AuthHandler_AuthHandlerFactory extends \WPMailSMTP\Vendor\Google\AuthHandler\AuthHandlerFactory
    {
    }
    class Google_AuthHandler_Guzzle6AuthHandler extends \WPMailSMTP\Vendor\Google\AuthHandler\Guzzle6AuthHandler
    {
    }
    class Google_AuthHandler_Guzzle7AuthHandler extends \WPMailSMTP\Vendor\Google\AuthHandler\Guzzle7AuthHandler
    {
    }
    class Google_Client extends \WPMailSMTP\Vendor\Google\Client
    {
    }
    class Google_Collection extends \WPMailSMTP\Vendor\Google\Collection
    {
    }
    class Google_Exception extends \WPMailSMTP\Vendor\Google\Exception
    {
    }
    class Google_Http_Batch extends \WPMailSMTP\Vendor\Google\Http\Batch
    {
    }
    class Google_Http_MediaFileUpload extends \WPMailSMTP\Vendor\Google\Http\MediaFileUpload
    {
    }
    class Google_Http_REST extends \WPMailSMTP\Vendor\Google\Http\REST
    {
    }
    class Google_Model extends \WPMailSMTP\Vendor\Google\Model
    {
    }
    class Google_Service extends \WPMailSMTP\Vendor\Google\Service
    {
    }
    class Google_Service_Exception extends \WPMailSMTP\Vendor\Google\Service\Exception
    {
    }
    class Google_Service_Resource extends \WPMailSMTP\Vendor\Google\Service\Resource
    {
    }
    class Google_Task_Exception extends \WPMailSMTP\Vendor\Google\Task\Exception
    {
    }
    interface Google_Task_Retryable extends \WPMailSMTP\Vendor\Google\Task\Retryable
    {
    }
    class Google_Task_Runner extends \WPMailSMTP\Vendor\Google\Task\Runner
    {
    }
    class Google_Utils_UriTemplate extends \WPMailSMTP\Vendor\Google\Utils\UriTemplate
    {
    }
}
