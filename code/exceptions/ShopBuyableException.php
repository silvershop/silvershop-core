<?php

/**
 * @package    shop
 * @subpackage exceptions
 * @deprecated 2.0 This class will be removed in the next major version.
 */
class ShopBuyableException extends Exception
{
    public function __construct($message, $code, Exception $previous)
    {
        Deprecation::notice('2.0', 'This class will be removed in the next major version.', Deprecation::SCOPE_CLASS);
        parent::__construct($message, $code, $previous);
    }
}
