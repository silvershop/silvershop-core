<?php

/**
 * Account page shows order history and a form to allow
 * the member to edit his/her details.
 *
 * @package shop
 */
class AccountPage extends Page
{
    private static $icon = 'silvershop/images/icons/account';

    public function canCreate($member = null, $context = array())
    {
        return !self::get()->exists();
    }

    /**
     * Returns the link or the URLSegment to the account page on this site
     *
     * @param boolean $urlSegment Return the URLSegment only
     */
    public static function find_link($urlSegment = false)
    {
        $page = self::get_if_account_page_exists();
        return ($urlSegment) ? $page->URLSegment : $page->Link();
    }

    /**
     * Return a link to view the order on the account page.
     *
     * @param int|string $orderID    ID of the order
     * @param boolean    $urlSegment Return the URLSegment only
     */
    public static function get_order_link($orderID, $urlSegment = false)
    {
        $page = self::get_if_account_page_exists();
        return ($urlSegment ? $page->URLSegment . '/' : $page->Link()) . 'order/' . $orderID;
    }

    protected static function get_if_account_page_exists()
    {
        if ($page = DataObject::get_one('AccountPage')) {
            return $page;
        }
        user_error(_t('AccountPage.NO_PAGE', 'No AccountPage was found. Please create one in the CMS!'), E_USER_ERROR);
    }

    /**
     * This module always requires a page model.
     */
    public function requireDefaultRecords()
    {
        parent::requireDefaultRecords();
        if (!self::get()->exists() && $this->config()->create_default_pages) {
            $page = self::create(
                array(
                    'Title'       => 'Account',
                    'URLSegment'  => AccountPage_Controller::config()->url_segment,
                    'ShowInMenus' => 0,
                )
            );
            $page->write();
            $page->publish('Stage', 'Live');
            $page->flushCache();
            DB::alteration_message('Account page created', 'created');
        }
    }
}

class AccountPage_Controller extends Page_Controller
{
    private static $url_segment     = 'account';

    private static $allowed_actions = array(
        'addressbook',
        'CreateAddressForm',
        'DefaultAddressForm',
        'editprofile',
        'EditAccountForm',
        'ChangePasswordForm',
        'changepassword', // redirects to editprofile
    );

    protected      $member;

    public function init()
    {
        parent::init();
        if (!Member::currentUserID()) {
            $messages = array(
                'default'    => _t(
                    'AccountPage.LOGIN',
                    'You\'ll need to login before you can access the account page.
					If you are not registered, you won\'t be able to access it until
					you make your first order, otherwise please enter your details below.'
                ),
                'logInAgain' => _t(
                    'AccountPage.LOGINAGAIN',
                    'You have been logged out. If you would like to log in again,
					please do so below.'
                ),
            );
            Security::permissionFailure($this, $messages);
            return false;
        }
        $this->member = Member::currentUser();
    }

    public function getTitle()
    {
        if ($this->dataRecord && $title = $this->dataRecord->Title) {
            return $title;
        }
        return _t('AccountPage.Title', "Account");
    }

    public function getMember()
    {
        return $this->member;
    }

    public function addressbook()
    {
        return array(
            'DefaultAddressForm' => $this->DefaultAddressForm(),
            'CreateAddressForm'  => $this->CreateAddressForm(),
        );
    }

    public function DefaultAddressForm()
    {
        $addresses = $this->member->AddressBook()->sort('Created', 'DESC');
        if ($addresses->exists()) {
            $fields = FieldList::create(
                DropdownField::create(
                    "DefaultShippingAddressID",
                    _t("Address.ShippingAddress", "Shipping Address"),
                    $addresses->map('ID', 'toString')->toArray()
                ),
                DropdownField::create(
                    "DefaultBillingAddressID",
                    _t("Address.BillingAddress", "Billing Address"),
                    $addresses->map('ID', 'toString')->toArray()
                )
            );
            $actions = FieldList::create(
                FormAction::create("savedefaultaddresses", _t("Address.SaveDefaults", "Save Defaults"))
            );
            $form = Form::create($this, "DefaultAddressForm", $fields, $actions);
            $form->loadDataFrom($this->member);

            return $form;
        }

        return false;
    }

    public function savedefaultaddresses($data, $form)
    {
        $form->saveInto($this->member);
        $this->member->write();

        $this->extend('updateDefaultAddressFormResponse', $form, $data, $response);

        return $response ?: $this->redirect($this->Link('addressbook'));
    }

    public function CreateAddressForm()
    {
        $singletonaddress = singleton('Address');
        $fields = $singletonaddress->getFrontEndFields();
        $actions = FieldList::create(
            FormAction::create("saveaddress", _t("Address.SaveNew", "Save New Address"))
        );
        $validator = RequiredFields::create($singletonaddress->getRequiredFields());
        $form = Form::create($this, "CreateAddressForm", $fields, $actions, $validator);
        $this->extend('updateCreateAddressForm', $form);
        return $form;
    }

    public function saveaddress($data, $form)
    {
        $member = $this->getMember();
        $address = Address::create();
        $form->saveInto($address);
        $address->MemberID = $member->ID;

        // Add value for Country if missing (due readonly field in form)
        if ($country = SiteConfig::current_site_config()->getSingleCountry()) {
            $address->Country = $country;
        }

        $address->write();

        if (!$member->DefaultShippingAddressID) {
            $member->DefaultShippingAddressID = $address->ID;
            $member->write();
        }
        if (!$member->DefaultBillingAddressID) {
            $member->DefaultBillingAddressID = $address->ID;
            $member->write();
        }
        $form->sessionMessage(_t("CreateAddressForm.SAVED", "Your address has been saved"), "good");

        $this->extend('updateCreateAddressFormResponse', $form, $data, $response);

        return $response ?: $this->redirect($this->Link('addressbook'));
    }

    public function editprofile()
    {
        return array();
    }

    /**
     * Return a form allowing the user to edit their details.
     *
     * @return ShopAccountForm
     */
    public function EditAccountForm()
    {
        return ShopAccountForm::create($this, 'EditAccountForm');
    }

    public function ChangePasswordForm()
    {
        $form = ChangePasswordForm::create($this, "ChangePasswordForm");
        $this->extend('updateChangePasswordForm', $form);
        $this->data()->extend('updateChangePasswordForm', $form);

        if ($this->data()->hasMethod('updateChangePasswordForm')) {  // if accessing through the model
            Deprecation::notice(
                '2.0',
                'Please access updateChangePasswordForm through AccountPage_Controller instead of AccountPage (this extension point is due to be removed)'
            );
        }

        return $form;
    }

    /**
     * By default, ChangePasswordForm redirects to /account/changepassword when it's done.
     * This catches that and sends it back to editprofile, which seems easier and less error-prone
     * than the alternative of trying to manipulate the BackURL field.
     */
    public function changepassword()
    {
        $this->redirect($this->Link('editprofile'));
    }
}
