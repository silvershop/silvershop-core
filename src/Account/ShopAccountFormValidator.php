<?php

namespace SilverShop\Core\Account;
use SilverStripe\Forms\RequiredFields;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;


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
            $uid = $data[$field];
            $currentMember = Security::getCurrentUser();

            //can't be taken
            if (Member::get()->filter($field, $uid)->exclude('ID', $currentMember->ID)->count() > 0) {
                // get localized field labels
                $fieldLabels = $currentMember->fieldLabels(false);
                // if a localized value exists, use this for our error-message
                $fieldLabel = isset($fieldLabels[$field]) ? $fieldLabels[$field] : $field;

                $this->validationError(
                    $field,
                    // re-use the message from checkout
                    _t(
                        'Checkout.MemberExists',
                        'A member already exists with the {Field} {Identifier}',
                        '',
                        array('Field' => $fieldLabel, 'Identifier' => $uid)
                    ),
                    "required"
                );
                $valid = false;
            }
        }
        return $valid;
    }
}
