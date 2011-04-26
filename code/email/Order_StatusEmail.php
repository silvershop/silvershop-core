<?php

/**
 * @Description: This class handles the status email which is sent after changing the attributes
 * in the report (eg. status changed to 'Shipped').
 *
 *
 * @authors: Silverstripe, Jeremy, Nicolaas
 *
 * @package: ecommerce
 * @sub-package: email
 *
 **/

class Order_StatusEmail extends Order_Email {

	protected $ss_template = 'Order_StatusEmail';

}
