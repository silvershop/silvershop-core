<?php

abstract class AddressCheckoutComponent extends CheckoutComponent
{
    /** @var string - Shipping or Billing */
    protected $addresstype;

    /** @var bool */
    private static $form_field_descriptions = true;

    /** @var bool */
    protected $formfielddescriptions = true;

    /** @var bool - allows this to be overridden by config */
    private static $add_to_addressbook = true;

    /** @var bool - allows this to be overridden at runtime */
    protected $addtoaddressbook;

    public function getFormFields(Order $order)
    {
        return $this->getAddress($order)->getFrontEndFields(
            array(
                'addfielddescriptions' => $this->useFormFieldDescriptions(),
            )
        );
    }

    public function getRequiredFields(Order $order)
    {
        return $this->getAddress($order)->getRequiredFields();
    }

    public function validateData(Order $order, array $data)
    {
    }

    public function getData(Order $order)
    {
        $data = $this->getAddress($order)->toMap();

        //merge data from multiple sources
        $data = array_merge(
            ShopUserInfo::singleton()->getLocation(),
            $data,
            array(
                $this->addresstype . "AddressID" => $order->{$this->addresstype . "AddressID"},
            )
        );

        //merge in default address if an address isn't available
        $member = Member::currentUser();
        if(!$order->{$this->addresstype . "AddressID"}) {
            $data = array_merge(
                ShopUserInfo::singleton()->getLocation(),
                $member ? $member->{"Default" . $this->addresstype . "Address"}()->toMap() : array(),
                array(
                    $this->addresstype . "AddressID" => $order->{$this->addresstype . "AddressID"},
                )
            );
        }

        unset($data['ID']);
        unset($data['ClassName']);
        unset($data['RecordClassName']);

        //ensure country is restricted if there is only one allowed country
        if ($country = SiteConfig::current_site_config()->getSingleCountry()) {
            $data['Country'] = $country;
        }

        return $data;
    }

    /**
     * Create a new address if the existing address has changed, or is not yet
     * created.
     *
     * @param Order $order order to get addresses from
     * @param array $data  data to set
     *
     * @return Order
     */
    public function setData(Order $order, array $data)
    {
        $address = $this->getAddress($order);
        //if the value matches the current address then unset
        //this is to fix issues with blank fields & the readonly Country field
        $addressfields = Address::database_fields(get_class($address));
        foreach($data as $key => $value) {
            if(!isset($addressfields[$key]) || (!$value && !$address->{$key})) {
                unset($data[$key]);
            }
        }
        $address->update($data);
        //if only one country is available, then set it
        if ($country = SiteConfig::current_site_config()->getSingleCountry()) {
            $address->Country = $country;
        }
        //write new address, or duplicate if changed
        if (!$address->isInDB()) {
            $address->write();
        } elseif ($address->isChanged()) {
            $address = $address->duplicate();
        }
        //set billing address, if not already set
        $order->{$this->addresstype . "AddressID"} = $address->ID;
        if (!$order->BillingAddressID) {
            $order->BillingAddressID = $address->ID;
        }
        $order->write();
        //update user info based on shipping address
        if ($this->addresstype === "Shipping") {
            ShopUserInfo::singleton()->setAddress($address);
            Zone::cache_zone_ids($address);
        }
        //associate member to address
        if ($member = Member::currentUser()) {
            $default = $member->{"Default" . $this->addresstype . "Address"}();
            //set default address
            if (!$default->exists()) {
                $member->{"Default" . $this->addresstype . "AddressID"} = $address->ID;
                $member->write();
            }
            if ($this->getAddToAddressBook()) {
                $member->AddressBook()->add($address);
            }
        }

        //extension hooks
        $order->extend('onSet' . $this->addresstype . 'Address', $address);

        return $order;
    }

    /**
     * Add new addresses to the address book.
     *
     * @param bool $add
     *
     * @return $this
     */
    public function setAddToAddressBook($add = true)
    {
        $this->addtoaddressbook = $add;
        return $this;
    }

    /**
     * @return bool
     */
    public function getAddToAddressBook()
    {
        if (isset($this->addtoaddressbook)) {
            return $this->addtoaddressbook;
        } else {
            return $this->config()->add_to_addressbook;
        }
    }

    /**
     * @return boolean
     */
    public function useFormFieldDescriptions()
    {
        if (isset($this->formfielddescriptions)) {
            return $this->formfielddescriptions;
        } else {
            return $this->config()->form_field_descriptions;
        }
    }

    /**
     * @param boolean $formfielddescriptions
     *
     * @return $this
     */
    public function setFormFieldDescriptions($formfielddescriptions)
    {
        $this->formfielddescriptions = $formfielddescriptions;
        return $this;
    }


    /**
     * @param Order $order
     *
     * @return Address
     */
    public function getAddress(Order $order)
    {
        return $order->{$this->addresstype . "Address"}();
    }
}

class ShippingAddressCheckoutComponent extends AddressCheckoutComponent
{
    protected $addresstype = "Shipping";
}

class BillingAddressCheckoutComponent extends AddressCheckoutComponent
{
    protected $addresstype = "Billing";
}
