<?php

class CheckoutConfig extends Object{
	
	private static $member_creation_enabled = true;
	private static $membership_required = false;

    /** @var bool - automatically save (tokenized) cards if available in the gateway */
    private static $save_credit_cards = false;

}