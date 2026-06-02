<?php

declare(strict_types=1);

namespace SilverShop\Control;

use JsonException;
use SilverShop\Cart\ShoppingCart;
use SilverShop\Interfaces\Buyable;
use SilverShop\Model\OrderItem;
use SilverShop\Page\Product;
use SilverShop\ShopTools;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\ClassInfo;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\SecurityToken;
use SilverStripe\Versioned\Versioned;
use SimpleXMLElement;

class WebServiceController extends Controller
{
    public function handleRequest(HTTPRequest $request): HTTPResponse
    {
        $this->setRequest($request);

        [$resource, $identifier, $format] = $this->parseRequest($request);
        if ($resource === 'products') {
            return $identifier === null
                ? $this->productsResponse($format)
                : $this->productResponse($identifier, $format);
        }

        if ($resource === 'cart') {
            return $this->cartResponse($request, $identifier, $format);
        }

        return $this->errorResponse($format, 404, 'Not found');
    }

    /**
     * @return array{0:string,1:?string,2:string}
     */
    private function parseRequest(HTTPRequest $request): array
    {
        $action = trim((string) $request->param('Action'));
        $identifier = trim((string) $request->param('ID'));
        $format = $this->requestedFormat($request);

        [$resource, $actionFormat] = $this->stripFormatSuffix($action);
        if ($actionFormat !== null) {
            $format = $actionFormat;
        }

        if ($identifier !== '') {
            [$identifier, $identifierFormat] = $this->stripFormatSuffix($identifier);
            if ($identifierFormat !== null) {
                $format = $identifierFormat;
            }
        }

        return [$resource, $identifier !== '' ? rawurldecode($identifier) : null, $format];
    }

    private function requestedFormat(HTTPRequest $request): string
    {
        $format = strtolower((string) $request->getVar('format'));
        if (in_array($format, ['json', 'xml'], true)) {
            return $format;
        }

        $accept = strtolower((string) $request->getHeader('Accept'));
        if (str_contains($accept, 'xml')) {
            return 'xml';
        }

        return 'json';
    }

    /**
     * @return array{0:string,1:?string}
     */
    private function stripFormatSuffix(string $segment): array
    {
        if (preg_match('/^(.*)\.(json|xml)$/i', $segment, $matches) === 1) {
            return [$matches[1], strtolower($matches[2])];
        }

        return [$segment, null];
    }

    private function productsResponse(string $format): HTTPResponse
    {
        $products = Versioned::get_by_stage(Product::class, Versioned::LIVE)->sort('Title');
        $payload = [];

        foreach ($products as $product) {
            if (!$product->canView()) {
                continue;
            }

            $payload[] = $this->serialiseProduct($product);
        }

        return $this->formatResponse($format, 'products', $payload);
    }

    private function productResponse(string $identifier, string $format): HTTPResponse
    {
        $products = Versioned::get_by_stage(Product::class, Versioned::LIVE);
        $product = preg_match('/^\d+$/', $identifier) === 1
            ? $products->byID((int) $identifier)
            : $products->filter('URLSegment', $identifier)->first();

        if (!$product || !$product->exists() || !$product->canView()) {
            return $this->errorResponse($format, 404, 'Product not found');
        }

        return $this->formatResponse($format, 'product', $this->serialiseProduct($product));
    }

    private function cartResponse(HTTPRequest $request, ?string $operation, string $format): HTTPResponse
    {
        $cart = ShoppingCart::singleton();

        if ($this->hasInvalidSecurityToken($request)) {
            return $this->cartOperationResponse(
                $format,
                $cart,
                false,
                400,
                [
                    'message' => 'Invalid security token, possible CSRF attack.',
                    'messageType' => 'bad',
                ]
            );
        }

        return match ($operation) {
            'add' => $this->cartAddResponse($request, $cart, $format),
            'remove' => $this->cartRemoveResponse($request, $cart, $format),
            'clear' => $this->cartClearResponse($cart, $format),
            default => $this->errorResponse($format, 404, 'Not found'),
        };
    }

    private function cartAddResponse(HTTPRequest $request, ShoppingCart $cart, string $format): HTTPResponse
    {
        $buyable = $this->buyableFromRequest($request, $cart);
        if (!$buyable instanceof Buyable) {
            return $this->cartOperationResponse(
                $format,
                $cart,
                false,
                404,
                ['message' => 'Not found', 'messageType' => 'bad']
            );
        }

        $quantity = (int) ($request->requestVar('quantity') ?? 1);
        if ($quantity < 0) {
            return $this->cartOperationResponse(
                $format,
                $cart,
                false,
                400,
                ['message' => 'Quantity must be zero or greater.', 'messageType' => 'bad']
            );
        }

        $result = $quantity === 0
            ? $cart->remove($buyable, null, $request->requestVars())
            : $cart->add($buyable, $quantity, $request->requestVars());

        if (!$result) {
            return $this->cartOperationResponse($format, $cart, false, 400);
        }

        $extra = [];
        if ($result instanceof OrderItem) {
            $extra['itemId'] = (int) $result->ID;
        }

        return $this->cartOperationResponse($format, $cart, true, 200, $extra);
    }

    private function cartRemoveResponse(HTTPRequest $request, ShoppingCart $cart, string $format): HTTPResponse
    {
        $buyable = $this->buyableFromRequest($request, $cart);
        if (!$buyable instanceof Buyable) {
            return $this->cartOperationResponse(
                $format,
                $cart,
                false,
                404,
                ['message' => 'Not found', 'messageType' => 'bad']
            );
        }

        $quantity = (int) ($request->requestVar('quantity') ?? 1);
        if ($quantity < 0) {
            return $this->cartOperationResponse(
                $format,
                $cart,
                false,
                400,
                ['message' => 'Quantity must be zero or greater.', 'messageType' => 'bad']
            );
        }

        $result = $cart->remove($buyable, $quantity, $request->requestVars());

        if ($result === null) {
            return $this->cartOperationResponse($format, $cart, false, 400);
        }

        if ($result === false) {
            return $this->cartOperationResponse($format, $cart, false, 404, ['message' => 'Not found']);
        }

        return $this->cartOperationResponse($format, $cart, true);
    }

    private function cartClearResponse(ShoppingCart $cart, string $format): HTTPResponse
    {
        $result = $cart->clear();
        if ($result === null) {
            return $this->cartOperationResponse($format, $cart, false, 400);
        }

        return $this->cartOperationResponse($format, $cart, true);
    }

    /**
     * @return array{id:int,title:string,price:float,link:string}
     */
    private function serialiseProduct(Product $product): array
    {
        $payload = [
            'id' => (int) $product->ID,
            'title' => (string) $product->Title,
            'price' => (float) $product->sellingPrice(),
            'link' => (string) $product->Link(),
        ];

        $this->extend('updateSerialisedProduct', $payload, $product);

        return $payload;
    }

    private function formatResponse(string $format, string $rootNode, array $payload, int $statusCode = 200): HTTPResponse
    {
        $rootNode = preg_replace('/[^A-Za-z0-9_-]/', '', $rootNode) ?: 'response';

        if ($format === 'xml') {
            $xml = new SimpleXMLElement(sprintf('<%s/>', $rootNode));
            $this->appendXml($xml, $payload, $rootNode === 'products' ? 'product' : 'field');
            $body = $xml->asXML();

            if ($body === false) {
                $response = HTTPResponse::create(
                    '{"message":"Failed to encode response data as XML"}',
                    500
                );
                $response->addHeader('Content-Type', 'application/json; charset=utf-8');
                return $response;
            }

            $response = HTTPResponse::create($body, $statusCode);
            $response->addHeader('Content-Type', 'application/xml; charset=utf-8');
            return $response;
        }

        try {
            $body = json_encode($payload, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            $body = '{"message":"Failed to encode response data as JSON"}';
            $statusCode = 500;
        }

        $response = HTTPResponse::create($body, $statusCode);
        $response->addHeader('Content-Type', 'application/json; charset=utf-8');
        return $response;
    }

    private function errorResponse(string $format, int $statusCode, string $message): HTTPResponse
    {
        return $this->formatResponse($format, 'response', ['message' => $message], $statusCode);
    }

    private function cartOperationResponse(
        string $format,
        ShoppingCart $cart,
        bool $success,
        int $statusCode = 200,
        array $extra = []
    ): HTTPResponse {
        $payload = array_merge(
            [
                'success' => $success,
                'message' => (string) $cart->getMessage(),
                'messageType' => $cart->getMessageType(),
            ],
            $extra
        );

        return $this->formatResponse($format, 'response', $payload, $statusCode);
    }

    private function hasInvalidSecurityToken(HTTPRequest $request): bool
    {
        return SecurityToken::is_enabled() && !SecurityToken::inst()->checkRequest($request);
    }

    private function buyableFromRequest(HTTPRequest $request, ShoppingCart $cart): ?Buyable
    {
        $buyableClass = (string) ($request->requestVar('Buyable') ?: Product::class);
        if (!ClassInfo::exists($buyableClass)) {
            $buyableClass = ShopTools::unsanitiseClassName($buyableClass);
        }
        $buyableId = (int) ($request->requestVar('ProductID') ?? $request->requestVar('BuyableID') ?? 0);

        if ($buyableId === 0 || !ClassInfo::exists($buyableClass)) {
            return null;
        }

        $buyable = $buyableClass::has_extension(Versioned::class)
            ? Versioned::get_by_stage($buyableClass, Versioned::LIVE)->byID($buyableId)
            : DataObject::get($buyableClass)->byID($buyableId);

        if (!$buyable instanceof Buyable) {
            return null;
        }

        return $cart->getCorrectBuyable($buyable);
    }

    private function appendXml(SimpleXMLElement $xml, mixed $payload, string $numericNodeName = 'item'): void
    {
        if (is_array($payload)) {
            foreach ($payload as $key => $value) {
                $nodeName = is_int($key) ? $numericNodeName : (string) $key;
                $child = $xml->addChild($nodeName);
                $this->appendXml($child, $value, $numericNodeName);
            }
            return;
        }

        $xml[0] = is_bool($payload) ? ($payload ? 'true' : 'false') : (string) $payload;
    }
}
