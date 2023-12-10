<?php

namespace SilverShop\Tests;

use Omnipay\Common\GatewayFactory;
use Omnipay\Common\Http\ClientInterface;
use SilverStripe\Dev\TestOnly;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

/**
 * Helper class to inject our desired HTTP client and requests into the newly created gateway instances.
 * @package SilverStripe\Omnipay\Tests\Service
 */
class TestGatewayFactory extends GatewayFactory implements TestOnly
{
    /**
     * HTTP client to use for gateways (for unit-tests)
     * @var ClientInterface
     */
    public static $httpClient;

    /**
     * HTTP request to use for gateways (for unit-tests)
     * @var HttpRequest
     */
    public static $httpRequest;

    public function create($class, ClientInterface $httpClient = null, HttpRequest $httpRequest = null)
    {
        return parent::create($class, self::$httpClient, self::$httpRequest);
    }
}
