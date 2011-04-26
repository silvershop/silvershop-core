<?php

/**
 * @Description: This class handles the receipt email which gets sent once an order is made.
 * You can call it by issuing sendReceipt() in the Order class.
 *
 * @authors: Silverstripe, Jeremy, Nicolaas
 *
 * @package: ecommerce
 * @sub-package: email
 *
 **/

class Order_ReceiptEmail extends Order_Email {

	protected $ss_template = 'Order_ReceiptEmail';

}

