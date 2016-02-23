<?php

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
            $requiredFields = $member->getValidator();
            $requiredFields->addRequiredField('Surname');
        } else {
            $fields = FieldList::create();
        }
        if (get_class($controller) == 'AccountPage_Controller') {
            $actions = FieldList::create(FormAction::create('submit', _t('MemberForm.SAVE', 'Save Changes')));
        } else {
            $actions = FieldList::create(
                FormAction::create('submit', _t('MemberForm.SAVE', 'Save Changes')),
                FormAction::create('proceed', _t('MemberForm.SAVEANDPROCEED', 'Save and proceed to checkout'))
            );
        }
        parent::__construct($controller, $name, $fields, $actions, $requiredFields);

        $this->extend('updateShopAccountForm');

        if ($record = $controller->data()) {
            $record->extend('updateShopAccountForm', $fields, $actions, $requiredFields);
        }

        if ($controller->data()
            && $controller->data()->hasMethod(
                'updateShopAccountForm'
            )
        ) {  // if accessing through the model
            Deprecation::notice(
                '2.0',
                'Please access updateShopAccountForm through ShopAccountForm instead of AccountPage (this extension point is due to be removed)'
            );
        }

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
        $form->sessionMessage(_t("MemberForm.DETAILSSAVED", 'Your details have been saved'), 'good');

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
        $form->sessionMessage(_t("MemberForm.DETAILSSAVED", 'Your details have been saved'), 'good');

        $this->extend('updateShopAccountFormResponse', $request, $form, $data, $response);

        return $response ?: $this->getController()->redirect(CheckoutPage::find_link());
    }
}

/**
 * Validates the shop account form.
 *
 * @subpackage forms
 */
class ShopAccountFormValidator extends RequiredFields
{
    /**
     * Ensures member unique id stays unique.
     */
    public function php($data)
    {
        $valid = parent::php($data);
        $field = (string)Member::config()->unique_identifier_field;
        if (isset($data[$field])) {
            $uid = $data[(string)Member::config()->unique_identifier_field];
            $currentmember = Member::currentUser();
            //can't be taken
            if (DataObject::get_one('Member', "$field = '$uid' AND ID != " . $currentmember->ID)) {
                // get localized field labels
                $fieldLabels = $currentmember->fieldLabels(false);
                // if a localized value exists, use this for our error-message
                $fieldLabel = isset($fieldLabels[$field]) ? $fieldLabels[$field] : $field;

                $this->validationError(
                    $field,
                    // re-use the message from checkout
                    sprintf(
                        _t("Checkout.MEMBEREXISTS", "A member already exists with the %s %s"),
                        $fieldLabel,
                        $uid
                    ),
                    "required"
                );
                $valid = false;
            }
        }
        return $valid;
    }
}
