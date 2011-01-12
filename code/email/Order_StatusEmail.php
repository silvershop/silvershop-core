<?php

/**
 * @Description: This class handles the status email which is sent after changing the attributes
 ** in the report (eg. status changed to 'Shipped').
 *
 * @package: ecommerce
 * @authors: Silverstripe, Jeremy, Nicolaas
 **/

class Order_StatusEmail extends Order_Email {

	protected $ss_template = 'Order_StatusEmail';

}
