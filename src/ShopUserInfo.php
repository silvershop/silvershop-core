<?php

namespace SilverShop;

use SilverShop\Model\Address;
use SilverStripe\Core\Injector\Injectable;

/**
 * Collects and stores data about the user. Keep this data in session.
 */
class ShopUserInfo
{
    use Injectable;

    /**
     * Get an array of location data
     *
     * @return array location data
     */
    public function getLocation(): array
    {
        return $this->getLocationData();
    }

    public function setLocation(array $location): static
    {
        $this->setLocationData($location);

        return $this;
    }

    /**
     * Get location of user
     */
    public function getAddress(): ?Address
    {
        $address = null;
        if (($data = $this->getLocationData()) !== []) {
            $address = Address::create();
            $address->update($data);
            $address->ID = 0; //ensure not in db
        }

        return $address;
    }

    /**
     * Set location of user
     *
     * @param  Address $address location
     * @return $this
     */
    public function setAddress(Address $address): static
    {
        $this->setLocationData($address->toMap());

        return $this;
    }

    protected function getLocationData(): array
    {
        $data = ShopTools::getSession()->get('UserInfo.Location');
        return is_array($data) ? $data : [];
    }

    protected function setLocationData(array $data): void
    {
        ShopTools::getSession()->set('UserInfo.Location', $data);
    }
}
