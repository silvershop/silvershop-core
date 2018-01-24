<?php

namespace SilverShop\Core\Account;


use SilverStripe\Security\Member;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\RequiredFields;



/**
 * Allows shop members to update their details with the shop.
 *
 * @package    shop
 * @subpackage forms
 */
class ShopAccountForm extends Form
{
    public function __construct($controller, $name)
    {
        $member = Member::currentUser();
        $requiredFields = null;
        if ($member && $member->exists()) {
            $fields = $member->getMemberFormFields();
            $fields->removeByName('Password');
            //TODO: This can be reverted to be $member->getValidator() as soon as this fix lands in framework
            // (most likely 3.4) https://github.com/silverstripe/silverstripe-framework/pull/5098
            $requiredFields = ShopAccountFormValidator::create();
            $requiredFields->addRequiredField('Surname');
        } else {
            $fields = FieldList::create();
        }
        if (get_class($controller) == 'AccountPage_Controller') {
            $actions = FieldList::create(FormAction::create('submit', _t('MemberForm.Save', 'Save Changes')));
        } else {
            $actions = FieldList::create(
                FormAction::create('submit', _t('MemberForm.Save', 'Save Changes')),
                FormAction::create('proceed', _t('MemberForm.SaveAndProceed', 'Save and proceed to checkout'))
            );
        }
        parent::__construct($controller, $name, $fields, $actions, $requiredFields);

        $this->extend('updateShopAccountForm');

        if ($member) {
            $member->Password = ""; //prevents password field from being populated with encrypted password data
            $this->loadDataFrom($member);
        }
    }

    /**
     * Save the changes to the form
     *
     * @param array          $data
     * @param Form           $form
     * @param SS_HTTPRequest $request
     *
     * @return bool|SS_HTTPResponse
     */
    public function submit($data, $form, $request)
    {
        $member = Member::currentUser();
        if (!$member) {
            return false;
        }

        $form->saveInto($member);
        $member->write();
        $form->sessionMessage(_t("MemberForm.DetailsSaved", 'Your details have been saved'), 'good');

        $this->extend('updateShopAccountFormResponse', $request, $form, $data, $response);

        return $response ?: $this->getController()->redirectBack();
    }

    /**
     * Save the changes to the form, and redirect to the checkout page
     *
     * @param array          $data
     * @param Form           $form
     * @param SS_HTTPRequest $request
     *
     * @return bool|SS_HTTPResponse
     */
    public function proceed($data, $form, $request)
    {
        $member = Member::currentUser();
        if (!$member) {
            return false;
        }

        $form->saveInto($member);
        $member->write();
        $form->sessionMessage(_t("MemberForm.DetailsSaved", 'Your details have been saved'), 'good');

        $this->extend('updateShopAccountFormResponse', $request, $form, $data, $response);

        return $response ?: $this->getController()->redirect(CheckoutPage::find_link());
    }
}

