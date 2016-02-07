<?php

/**
 * Collects and stores data about the user. Keep this data in session.
 */
class ShopUserInfo
{
    public static function singleton()
    {
        static $singleton = null;
        if ($singleton === null) {
            $singleton = new ShopUserInfo();
        }
        return $singleton;
    }

    private function __construct()
    {
    }

    /**
     * Get an array of location data
     *
     * @return array location data
     */
    public function getLocation()
    {
        return $this->getLocationData();
    }

    public function setLocation(array $location)
    {
        $this->setLocationData($location);

        return $this;
    }

    /**
     * Get location of user
     *
     * @param Address $address location
     */
    public function getAddress()
    {
        $address = null;
        if ($data = $this->getLocationData()) {
            $address = Address::create();
            $address->update($data);
            $address->ID = 0; //ensure not in db
        }

        return $address;
    }

    /**
     * Set location of user
     *
     * @param Address $address location
     */
    public function setAddress(Address $address)
    {
        $this->setLocationData($address->toMap());

        return $this;
    }

    protected function getLocationData()
    {
        $data = Session::get("UserInfo.Location");
        return is_array($data) ? $data : array();
    }

    protected function setLocationData(array $data)
    {
        Session::set("UserInfo.Location", Convert::raw2sql($data));
    }
}
