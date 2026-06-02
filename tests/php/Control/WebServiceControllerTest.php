<?php

declare(strict_types=1);

namespace SilverShop\Tests\Control;

use SilverShop\Page\Product;
use SilverShop\Page\ProductCategory;
use SilverShop\Tests\ShopTestBootstrap;
use SilverStripe\Dev\FunctionalTest;
use SilverStripe\Versioned\Versioned;

final class WebServiceControllerTest extends FunctionalTest
{
    protected static $fixture_file = __DIR__ . '/../Fixtures/shop.yml';

    protected static bool $disable_theme = true;

    protected function setUp(): void
    {
        parent::setUp();

        ShopTestBootstrap::setConfiguration();
        $this->logInWithPermission('ADMIN');

        foreach (['products', 'clothing', 'electronics', 'musicplayers', 'clearance'] as $category) {
            $this->objFromFixture(ProductCategory::class, $category)->publishSingle();
        }

        foreach (['socks', 'tshirt', 'hdtv', 'beachball', 'mp3player', 'pdfbrochure'] as $product) {
            $this->objFromFixture(Product::class, $product)->publishSingle();
        }

        $this->logOut();
        Versioned::set_stage('Live');
    }

    public function testProductsJsonList(): void
    {
        $response = $this->get('api/v1/products.json');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringContainsString('application/json', (string) $response->getHeader('Content-Type'));

        $payload = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($payload);

        $titles = array_column($payload, 'title');
        $this->assertContains('Socks', $titles);
        $this->assertContains('Mp3 Player', $titles);

        $socks = current(array_filter($payload, static fn(array $product): bool => $product['title'] === 'Socks'));
        $this->assertIsArray($socks);
        $this->assertSame(8.0, (float) $socks['price']);
    }

    public function testSingleProductJsonByUrlSegment(): void
    {
        $response = $this->get('api/v1/products/socks.json');

        $this->assertSame(200, $response->getStatusCode());

        $payload = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($payload);
        $this->assertSame('Socks', $payload['title']);
        $this->assertSame(8.0, (float) $payload['price']);
        $this->assertStringContainsString('/socks', $payload['link']);
    }

    public function testProductsXmlList(): void
    {
        $response = $this->get('api/v1/products.xml');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringContainsString('application/xml', (string) $response->getHeader('Content-Type'));
        $this->assertStringContainsString('<products>', (string) $response->getBody());
        $this->assertStringContainsString('<title>Socks</title>', (string) $response->getBody());
    }
}
