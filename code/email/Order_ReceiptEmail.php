<?php

/**
 * @Description: This class handles the receipt email which gets sent once an order is made.
 ** You can call it by issuing sendReceipt() in the Order class.
 * @package: ecommerce
 * @authors: Silverstripe, Jeremy, Nicolaas
 **/

class Order_ReceiptEmail extends Order_Email {

	protected $ss_template = 'Order_ReceiptEmail';

}

