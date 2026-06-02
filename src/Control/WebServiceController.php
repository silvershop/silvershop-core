<?php

declare(strict_types=1);

namespace SilverShop\Control;

use SilverShop\Page\Product;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Versioned\Versioned;
use SimpleXMLElement;

class WebServiceController extends Controller
{
    public function handleRequest(HTTPRequest $request): HTTPResponse
    {
        $this->setRequest($request);

        [$resource, $identifier, $format] = $this->parseRequest($request);
        if ($resource !== 'products') {
            return $this->errorResponse($format, 404, 'Not found');
        }

        return $identifier === null
            ? $this->productsResponse($format)
            : $this->productResponse($identifier, $format);
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

        $accept = strtolower((string) $request->getHeader('Accept', true));
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
        $product = ctype_digit($identifier)
            ? $products->byID((int) $identifier)
            : $products->filter('URLSegment', $identifier)->first();

        if (!$product || !$product->exists() || !$product->canView()) {
            return $this->errorResponse($format, 404, 'Product not found');
        }

        return $this->formatResponse($format, 'product', $this->serialiseProduct($product));
    }

    /**
     * @return array{id:int,title:string,price:float,link:string}
     */
    private function serialiseProduct(Product $product): array
    {
        return [
            'id' => (int) $product->ID,
            'title' => (string) $product->Title,
            'price' => (float) $product->sellingPrice(),
            'link' => (string) $product->Link(),
        ];
    }

    private function formatResponse(string $format, string $rootNode, array $payload, int $statusCode = 200): HTTPResponse
    {
        if ($format === 'xml') {
            $xml = new SimpleXMLElement(sprintf('<%s/>', $rootNode));
            $this->appendXml($xml, $payload, $rootNode === 'products' ? 'product' : 'field');

            $response = HTTPResponse::create($xml->asXML() ?: '', $statusCode);
            $response->addHeader('Content-Type', 'application/xml; charset=utf-8');
            return $response;
        }

        $response = HTTPResponse::create(json_encode($payload), $statusCode);
        $response->addHeader('Content-Type', 'application/json; charset=utf-8');
        return $response;
    }

    private function errorResponse(string $format, int $statusCode, string $message): HTTPResponse
    {
        return $this->formatResponse($format, 'response', ['message' => $message], $statusCode);
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
