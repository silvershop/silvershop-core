<?php

namespace SilverShop\Forms;

use SilverShop\Extension\ShopConfigExtension;
use SilverShop\Page\AccountPageController;
use SilverShop\Page\CheckoutPage;
use SilverStripe\Core\Config\Config;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SilverStripe\Security\Security;

/**
 * Allows shop members to update their details with the shop.
 */
class ShopAccountForm extends Form
{
    public function __construct($controller, $name)
    {
        $member = Security::getCurrentUser();
        $requiredFields = null;
        if ($member && $member->exists()) {
            $fields = $member->getMemberFormFields();
            $fields->removeByName('Password');
            $requiredFields = $member->getValidator();
            $requiredFields->addRequiredField('Surname');
        } else {
            $fields = FieldList::create();
        }
        if ($controller instanceof AccountPageController) {
            $actions = FieldList::create(FormAction::create('submit', _t(__CLASS__ . '.Save', 'Save Changes')));
        } else {
            $actions = FieldList::create(
                FormAction::create('submit', _t(__CLASS__ . '.Save', 'Save Changes'))
                    ->setUseButtonTag(Config::inst()->get(ShopConfigExtension::class, 'forms_use_button_tag')),
                FormAction::create('proceed', _t(__CLASS__ . '.SaveAndProceed', 'Save and proceed to checkout'))
                    ->setUseButtonTag(Config::inst()->get(ShopConfigExtension::class, 'forms_use_button_tag'))
            );
        }
        parent::__construct($controller, $name, $fields, $actions, $requiredFields);

        $this->extend('updateShopAccountForm');

        if ($member) {
            $this->loadDataFrom($member);
        }
    }

    /**
     * Save the changes to the form
     *
     * @param array       $data
     * @param Form        $form
     * @param HTTPRequest $request
     *
     * @return bool|HTTPResponse
     * @throws \SilverStripe\ORM\ValidationException
     */
    public function submit($data, $form, $request)
    {
        $member = Security::getCurrentUser();
        if (!$member) {
            return false;
        }

        $form->saveInto($member);
        $member->write();
        $form->sessionMessage(_t(__CLASS__ . '.DetailsSaved', 'Your details have been saved'), 'good');

        $this->extend('updateShopAccountFormResponse', $request, $form, $data, $response);

        return $response ?: $this->getController()->redirectBack();
    }

    /**
     * Save the changes to the form, and redirect to the checkout page
     *
     * @param array       $data
     * @param Form        $form
     * @param HTTPRequest $request
     *
     * @return bool|HTTPResponse
     * @throws \SilverStripe\ORM\ValidationException
     */
    public function proceed($data, $form, $request)
    {
        $member = Security::getCurrentUser();
        if (!$member) {
            return false;
        }

        $form->saveInto($member);
        $member->write();
        $form->sessionMessage(_t(__CLASS__ . '.DetailsSaved', 'Your details have been saved'), 'good');

        $this->extend('updateShopAccountFormResponse', $request, $form, $data, $response);

        return $response ?: $this->getController()->redirect(CheckoutPage::find_link());
    }
}
