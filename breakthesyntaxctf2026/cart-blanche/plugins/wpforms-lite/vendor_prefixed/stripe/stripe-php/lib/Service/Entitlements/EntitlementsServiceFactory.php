<?php

// File generated from our OpenAPI spec
namespace WPForms\Vendor\Stripe\Service\Entitlements;

/**
 * Service factory class for API resources in the Entitlements namespace.
 *
 * @property ActiveEntitlementService $activeEntitlements
 * @property FeatureService $features
 */
class EntitlementsServiceFactory extends \WPForms\Vendor\Stripe\Service\AbstractServiceFactory
{
    /**
     * @var array<string, string>
     */
    private static $classMap = ['activeEntitlements' => ActiveEntitlementService::class, 'features' => FeatureService::class];
    protected function getServiceClass($name)
    {
        return \array_key_exists($name, self::$classMap) ? self::$classMap[$name] : null;
    }
}
