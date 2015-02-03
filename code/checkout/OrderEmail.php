<?php

/**
 * This class handles the receipt email which gets sent once an order is made.
 * You can call it by issuing sendReceipt() in the Order class.
 * @deprecated 2.0 Use Email instead, and set template
 */
class Order_ReceiptEmail extends Email {

	protected $ss_template = 'Order_ReceiptEmail';
}

/**
 * This class handles the status email which is sent after changing the attributes
 * in the report (eg. status changed to 'Shipped').
 * @deprecated 2.0 Use Email instead, and set template
 */
class Order_StatusEmail extends Email {

	protected $ss_template = 'Order_StatusEmail';

}
