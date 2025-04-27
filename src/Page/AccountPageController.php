<?php

namespace SilverShop\Page;

use SilverShop\Extension\OrderManipulationExtension;
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

/**
 * @mixin OrderManipulationExtension
 * @extends PageController<AccountPage>
 */
class AccountPageController extends PageController
{
    private static string $url_segment = 'account';

    private static array $allowed_actions = [
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

    public function init(): void
    {
        parent::init();

        $this->member = Security::getCurrentUser();

        if (!$this->member) {
            $messages = [
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
            ];
            Security::permissionFailure($this, $messages);
        }
    }

    public function getTitle(): string
    {
        if ($this->dataRecord && $title = $this->dataRecord->Title) {
            return $title;
        }
        return _t('SilverShop\Page\AccountPage.DefaultTitle', 'Account');
    }

    public function getMember(): Member
    {
        return $this->member;
    }

    public function addressbook(): array
    {
        return [
            'DefaultAddressForm' => $this->DefaultAddressForm(),
            'CreateAddressForm' => $this->CreateAddressForm(),
        ];
    }

    public function DefaultAddressForm(): Form|bool
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

    public function savedefaultaddresses($data, $form): HTTPResponse
    {
        $form->saveInto($this->member);
        $this->member->write();

        $response = null;
        $this->extend('updateDefaultAddressFormResponse', $form, $data, $response);

        return $response ?: $this->redirect($this->Link('addressbook'));
    }

    public function CreateAddressForm(): Form
    {
        $singletonaddress = singleton(Address::class);
        $fields = $singletonaddress->getFrontEndFields();
        $fieldList = FieldList::create(
            FormAction::create('saveaddress', _t('SilverShop\Model\Address.SaveNew', 'Save New Address'))
        );
        $requiredFields = RequiredFields::create($singletonaddress->getRequiredFields());
        $form = Form::create($this, 'CreateAddressForm', $fields, $fieldList, $requiredFields);
        $this->extend('updateCreateAddressForm', $form);
        return $form;
    }

    public function saveaddress($data, $form): HTTPResponse
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

        $response = null;
        $this->extend('updateCreateAddressFormResponse', $form, $data, $response);

        return $response ?: $this->redirect($this->Link('addressbook'));
    }

    public function editprofile(): array
    {
        return [];
    }

    function deleteaddress(HTTPRequest$httpRequest): ?HTTPResponse
    {
        // NOTE: we don't want to fully delete the address because it's presumably still
        // attached to an order. Setting MemberID to 0 means it won't show up in the address
        // book any longer.
        $address = $this->member->AddressBook()->byID($httpRequest->param('ID'));
        if ($address) {
            $address->MemberID = 0;
            $address->write();
        } else {
            return $this->httpError(404, 'Address not found');
        }
        return $this->redirectBack();
    }

    function setdefaultbilling(HTTPRequest $httpRequest): HTTPResponse
    {
        $this->member->DefaultBillingAddressID = $httpRequest->param('ID');
        $this->member->write();
        return $this->redirectBack();
    }

    function setdefaultshipping(HTTPRequest $httpRequest): HTTPResponse
    {
        $this->member->DefaultShippingAddressID = $httpRequest->param('ID');
        $this->member->write();
        return $this->redirectBack();
    }

    /**
     * Return a form allowing the user to edit their details.
     */
    public function EditAccountForm(): ShopAccountForm
    {
        return ShopAccountForm::create($this, 'EditAccountForm');
    }

    public function ChangePasswordForm(): ChangePasswordForm
    {
        /**
         * @var ChangePasswordHandler $handler
         */
        $handler = Injector::inst()->get(MemberAuthenticator::class)->getChangePasswordHandler($this->Link());
        $handler->setRequest($this->getRequest());
        /**
         * @var ChangePasswordForm $changePasswordForm
         */
        $changePasswordForm = $handler->changePasswordForm();

        // The default form tries to redirect to /account/login which doesn't exist
        $backURL = $changePasswordForm->Fields()->fieldByName('BackURL');
        if (!$backURL) {
            $backURL = HiddenField::create('BackURL', 'BackURL');
            $changePasswordForm->Fields()->push($backURL);
        }
        $backURL->setValue($this->Link('editprofile'));


        $this->extend('updateChangePasswordForm', $changePasswordForm);

        return $changePasswordForm;
    }

    /**
     * By default, ChangePasswordForm redirects to /account/changepassword when it's done.
     * This catches that and sends it back to editprofile, which seems easier and less error-prone
     * than the alternative of trying to manipulate the BackURL field.
     */
    public function changepassword(): void
    {
        $this->redirect($this->Link('editprofile'));
    }
}
