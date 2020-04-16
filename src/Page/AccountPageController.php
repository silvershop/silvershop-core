<?php

namespace SilverShop\Page;

use PageController;
use SilverShop\Forms\ShopAccountForm;
use SilverShop\Model\Address;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\HiddenField;
use SilverStripe\Forms\RequiredFields;
use SilverStripe\Security\Member;
use SilverStripe\Security\MemberAuthenticator\ChangePasswordForm;
use SilverStripe\Security\MemberAuthenticator\ChangePasswordHandler;
use SilverStripe\Security\MemberAuthenticator\MemberAuthenticator;
use SilverStripe\Security\Security;
use SilverStripe\SiteConfig\SiteConfig;

class AccountPageController extends PageController
{
    private static $url_segment = 'account';

    private static $allowed_actions = [
        'addressbook',
        'CreateAddressForm',
        'DefaultAddressForm',
        'editprofile',
        'EditAccountForm',
        'ChangePasswordForm',
        'changepassword', // redirects to editprofile
        'deleteaddress',
        'setdefaultbilling',
        'setdefaultshipping',
    ];

    /**
     * @var Member
     */
    protected $member;

    public function init()
    {
        parent::init();

        $this->member = Security::getCurrentUser();

        if (!$this->member) {
            $messages = array(
                'default' => _t(
                    'SilverShop\Page\AccountPage.Login',
                    'You\'ll need to login before you can access the account page.
                    If you are not registered, you won\'t be able to access it until
                    you make your first order, otherwise please enter your details below.'
                ),
                'logInAgain' => _t(
                    'SilverShop\Page\AccountPage.LoginAgain',
                    'You have been logged out. If you would like to log in again, please do so below.'
                ),
            );
            Security::permissionFailure($this, $messages);
        }
    }

    public function getTitle()
    {
        if ($this->dataRecord && $title = $this->dataRecord->Title) {
            return $title;
        }
        return _t('SilverShop\Page\AccountPage.DefaultTitle', 'Account');
    }

    public function getMember()
    {
        return $this->member;
    }

    public function addressbook()
    {
        return array(
            'DefaultAddressForm' => $this->DefaultAddressForm(),
            'CreateAddressForm' => $this->CreateAddressForm(),
        );
    }

    public function DefaultAddressForm()
    {
        $addresses = $this->member->AddressBook()->sort('Created', 'DESC');
        if ($addresses->exists()) {
            $fields = FieldList::create(
                DropdownField::create(
                    'DefaultShippingAddressID',
                    _t('SilverShop\Model\Address.ShippingAddress', 'Shipping Address'),
                    $addresses->map('ID', 'toString')->toArray()
                ),
                DropdownField::create(
                    'DefaultBillingAddressID',
                    _t('SilverShop\Model\Address.BillingAddress', 'Billing Address'),
                    $addresses->map('ID', 'toString')->toArray()
                )
            );
            $actions = FieldList::create(
                FormAction::create('savedefaultaddresses', _t('SilverShop\Model\Address.SaveDefaults', 'Save Defaults'))
            );
            $form = Form::create($this, 'DefaultAddressForm', $fields, $actions);
            $form->loadDataFrom($this->member);

            $this->extend('updateDefaultAddressForm', $form);

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
        $singletonaddress = singleton(Address::class);
        $fields = $singletonaddress->getFrontEndFields();
        $actions = FieldList::create(
            FormAction::create('saveaddress', _t('SilverShop\Model\Address.SaveNew', 'Save New Address'))
        );
        $validator = RequiredFields::create($singletonaddress->getRequiredFields());
        $form = Form::create($this, 'CreateAddressForm', $fields, $actions, $validator);
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
        $form->sessionMessage(_t('SilverShop\Model\Address.AddressSaved', 'Your address has been saved'), 'good');

        $this->extend('updateCreateAddressFormResponse', $form, $data, $response);

        return $response ?: $this->redirect($this->Link('addressbook'));
    }

    public function editprofile()
    {
        return array();
    }

    /**
     * @param HTTPRequest $req
     * @return HTTPResponse
     */
    function deleteaddress($req)
    {
        // NOTE: we don't want to fully delete the address because it's presumably still
        // attached to an order. Setting MemberID to 0 means it won't show up in the address
        // book any longer.
        $address = $this->member->AddressBook()->byID($req->param('ID'));
        if ($address) {
            $address->MemberID = 0;
            $address->write();
        } else {
            return $this->httpError(404, 'Address not found');
        }
        return $this->redirectBack();
    }

    /**
     * @param HTTPRequest $req
     * @return HTTPResponse
     */
    function setdefaultbilling($req)
    {
        $this->member->DefaultBillingAddressID = $req->param('ID');
        $this->member->write();
        return $this->redirectBack();
    }

    /**
     * @param HTTPRequest $req
     * @return HTTPResponse
     */
    function setdefaultshipping($req)
    {
        $this->member->DefaultShippingAddressID = $req->param('ID');
        $this->member->write();
        return $this->redirectBack();
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

        /**
         * @var ChangePasswordHandler $handler
         */
        $handler = Injector::inst()->get(MemberAuthenticator::class)->getChangePasswordHandler($this->Link());
        $handler->setRequest($this->getRequest());
        /**
         * @var ChangePasswordForm $form
         */
        $form = $handler->changePasswordForm();

        // The default form tries to redirect to /account/login which doesn't exist
        $backURL = $form->Fields()->fieldByName('BackURL');
        if (!$backURL) {
            $backURL = new HiddenField('BackURL', 'BackURL');
            $form->Fields()->push($backURL);
        }
        $backURL->setValue($this->Link('editprofile'));


        $this->extend('updateChangePasswordForm', $form);

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
