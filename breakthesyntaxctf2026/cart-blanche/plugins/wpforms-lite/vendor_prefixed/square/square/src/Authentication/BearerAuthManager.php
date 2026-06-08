<?php

declare (strict_types=1);
namespace WPForms\Vendor\Square\Authentication;

use WPForms\Vendor\Core\Authentication\CoreAuth;
use WPForms\Vendor\Square\ConfigurationDefaults;
use WPForms\Vendor\Core\Request\Parameters\HeaderParam;
use WPForms\Vendor\Core\Utils\CoreHelper;
use WPForms\Vendor\Square\BearerAuthCredentials;
/**
 * Utility class for authorization and token management.
 */
class BearerAuthManager extends CoreAuth implements BearerAuthCredentials
{
    /**
     * @var array
     */
    private $config;
    public function __construct(array $config)
    {
        $this->config = $config;
        parent::__construct(HeaderParam::init('Authorization', CoreHelper::getBearerAuthString($this->getAccessToken()))->requiredNonEmpty());
    }
    /**
     * String value for accessToken.
     */
    public function getAccessToken() : string
    {
        return $this->config['accessToken'] ?? ConfigurationDefaults::ACCESS_TOKEN;
    }
    /**
     * Checks if provided credentials match with existing ones.
     *
     * @param string $accessToken The OAuth 2.0 Access Token to use for API requests.
     */
    public function equals(string $accessToken) : bool
    {
        return $accessToken == $this->getAccessToken();
    }
}
