<?php

declare(strict_types=1);

namespace SilverShop\Tests\Control;

use SilverShop\Cart\ShoppingCart;
use SilverShop\Control\WebServiceController;
use SilverShop\Page\Product;
use SilverShop\Page\ProductCategory;
use SilverShop\Tests\ShopTestBootstrap;
use SilverStripe\Core\Extension;
use SilverStripe\Dev\FunctionalTest;
use SilverStripe\Security\SecurityToken;
use SilverStripe\Versioned\Versioned;

final class WebServiceControllerTest extends FunctionalTest
{
    protected static $fixture_file = __DIR__ . '/../Fixtures/shop.yml';

    protected static bool $disable_theme = true;

    protected function setUp(): void
    {
        parent::setUp();

        ShopTestBootstrap::setConfiguration();
        ShoppingCart::singleton()->clear();
        WebServiceController::add_extension(WebServiceControllerTest_SerialisedProductExtension::class);
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

    protected function tearDown(): void
    {
        WebServiceController::remove_extension(WebServiceControllerTest_SerialisedProductExtension::class);
        WebServiceControllerTest_SerialisedProductExtension::$enabled = false;
        parent::tearDown();
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

        $matches = array_values(array_filter($payload, static fn(array $product): bool => $product['title'] === 'Socks'));
        $this->assertNotEmpty($matches);

        $socks = $matches[0];
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

    public function testProductSerialisationCanBeExtended(): void
    {
        WebServiceControllerTest_SerialisedProductExtension::$enabled = true;

        $response = $this->get('api/v1/products/socks.json');

        $payload = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($payload);
        $this->assertSame('custom-socks', $payload['customTag']);
    }

    public function testCartAddJson(): void
    {
        $product = $this->objFromFixture(Product::class, 'socks');
        $response = $this->get($this->apiUrl('api/v1/cart/add.json', [
            'ProductID' => $product->ID,
            'quantity' => 2,
        ]));

        $this->assertSame(200, $response->getStatusCode());

        $payload = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($payload);
        $this->assertTrue($payload['success']);
        $this->assertSame('good', $payload['messageType']);
        $this->assertArrayHasKey('itemId', $payload);

        $item = ShoppingCart::singleton()->get($product);
        $this->assertNotNull($item);
        $this->assertSame(2, (int) $item->Quantity);
    }

    public function testCartRemoveJson(): void
    {
        $product = $this->objFromFixture(Product::class, 'socks');
        ShoppingCart::singleton()->add($product, 2);

        $response = $this->get($this->apiUrl('api/v1/cart/remove.json', [
            'ProductID' => $product->ID,
        ]));

        $this->assertSame(200, $response->getStatusCode());

        $payload = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($payload);
        $this->assertTrue($payload['success']);
        $this->assertSame('good', $payload['messageType']);

        $item = ShoppingCart::singleton()->get($product);
        $this->assertNotNull($item);
        $this->assertSame(1, (int) $item->Quantity);
    }

    public function testCartClearJson(): void
    {
        $product = $this->objFromFixture(Product::class, 'socks');
        ShoppingCart::singleton()->add($product, 1);

        $response = $this->get($this->apiUrl('api/v1/cart/clear.json'));

        $this->assertSame(200, $response->getStatusCode());

        $payload = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($payload);
        $this->assertTrue($payload['success']);
        $this->assertSame('good', $payload['messageType']);
        $this->assertNull(ShoppingCart::singleton()->current());
    }

    public function testCartRemoveJsonWhenItemMissing(): void
    {
        $product = $this->objFromFixture(Product::class, 'socks');
        $response = $this->get($this->apiUrl('api/v1/cart/remove.json', [
            'ProductID' => $product->ID,
        ]));

        $this->assertSame(404, $response->getStatusCode());

        $payload = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($payload);
        $this->assertFalse($payload['success']);
        $this->assertSame('bad', $payload['messageType']);
        $this->assertSame('Not found', $payload['message']);
    }

    public function testCartAddRejectsNegativeQuantity(): void
    {
        $product = $this->objFromFixture(Product::class, 'socks');
        $response = $this->get($this->apiUrl('api/v1/cart/add.json', [
            'ProductID' => $product->ID,
            'quantity' => -1,
        ]));

        $this->assertSame(400, $response->getStatusCode());

        $payload = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($payload);
        $this->assertFalse($payload['success']);
        $this->assertSame('bad', $payload['messageType']);
        $this->assertSame('Quantity must be zero or greater.', $payload['message']);
        $this->assertNull(ShoppingCart::singleton()->get($product));
    }

    /**
     * @param array<string, mixed> $query
     */
    private function apiUrl(string $path, array $query = []): string
    {
        if (SecurityToken::is_enabled()) {
            $query[SecurityToken::inst()->getName()] = SecurityToken::inst()->getValue();
        }

        return $query ? $path . '?' . http_build_query($query) : $path;
    }
}

class WebServiceControllerTest_SerialisedProductExtension extends Extension
{
    public static bool $enabled = false;

    /**
     * @param array<string, mixed> $payload
     */
    public function updateSerialisedProduct(array &$payload, Product $product): void
    {
        if (!self::$enabled) {
            return;
        }

        $payload['customTag'] = 'custom-socks';
    }
}
