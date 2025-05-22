<?php

namespace SilverShop\Extension;

use SilverShop\Cart\ShoppingCart;
use SilverShop\Model\Address;
use SilverShop\Model\Order;
use SilverStripe\Core\Extension;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\HasManyList;
use SilverStripe\Security\Member;
use SilverStripe\SiteConfig\SiteConfig;

/**
 * ShopMember provides customisations to {@link Member} for shop purposes
 * @property int $DefaultShippingAddressID
 * @property int $DefaultBillingAddressID
 * @method Address DefaultShippingAddress()
 * @method Address DefaultBillingAddress()
 * @method HasManyList<Address> AddressBook()
 * @extends Extension<(Member & static)>
 */
class MemberExtension extends Extension
{
    private static bool $login_joins_cart = true;

    private static array $has_many = [
        'AddressBook' => Address::class,
    ];

    private static array $has_one = [
        'DefaultShippingAddress' => Address::class,
        'DefaultBillingAddress' => Address::class,
    ];

    /**
     * Get member by unique field.
     */
    public static function get_by_identifier($idvalue): ?Member
    {
        return Member::get()->filter(
            Member::config()->unique_identifier_field,
            $idvalue
        )->first();
    }

    public function updateCMSFields(FieldList $fieldList): void
    {
        $fieldList->removeByName('Country');
        $fieldList->removeByName('DefaultShippingAddressID');
        $fieldList->removeByName('DefaultBillingAddressID');
        $fieldList->addFieldToTab(
            'Root.Main',
            DropdownField::create(
                'Country',
                _t('SilverShop\Model\Address.db_Country', 'Country'),
                SiteConfig::current_site_config()->getCountriesList()
            )
        );
    }

    public function updateMemberFormFields($fields): void
    {
        $fields->removeByName('DefaultShippingAddressID');
        $fields->removeByName('DefaultBillingAddressID');
        if ($gender = $fields->dataFieldByName('Gender')) {
            $gender->setHasEmptyDefault(true);
        }
    }

    /**
     * Link the current order to the current member on login,
     * if there is one, and if configuration is set to do so.
     */
    public function afterMemberLoggedIn(): void
    {
        if (Member::config()->login_joins_cart && $order = ShoppingCart::singleton()->current()) {
            $order->MemberID = $this->owner->ID;
            $order->write();
        }
    }

    /**
     * Clear the cart, and session variables on member logout
     */
    public function beforeMemberLoggedOut(): void
    {
        if (Member::config()->login_joins_cart) {
            ShoppingCart::singleton()->clear();
        }
    }

    /**
     * Get the past orders for this member
     *
     * @return DataList list of orders
     */
    public function getPastOrders(): DataList
    {
        return Order::get()
            ->filter('MemberID', $this->owner->ID)
            ->filter('Status:not', Order::config()->hidden_status);
    }
}
