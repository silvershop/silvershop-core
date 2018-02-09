<?php

namespace SilverShop\Checkout;

use SilverShop\Extension\MemberExtension;
use SilverStripe\Core\Config\Config;
use SilverStripe\ORM\ValidationException;
use SilverStripe\ORM\ValidationResult;
use SilverStripe\Security\Member;

class ShopMemberFactory
{
    /**
     * Create member account from data array.
     * Data must contain unique identifier.
     *
     * @throws ValidationException
     *
     * @param $data - map of member data
     *
     * @return Member - new member (not saved to db)
     */
    public function create($data)
    {
        $result = ValidationResult::create();
        if (!Checkout::member_creation_enabled()) {
            $result->addError(
                _t('SilverShop\Checkout\Checkout.MembershipIsNotAllowed', 'Creating new memberships is not allowed')
            );
            throw new ValidationException($result);
        }
        $idfield = Config::inst()->get(Member::class, 'unique_identifier_field');
        if (!isset($data[$idfield]) || empty($data[$idfield])) {
            $result->addError(
                _t(
                    'SilverShop\Checkout\Checkout.IdFieldNotFound',
                    'Required field not found: {IdentifierField}',
                    'Identifier is the field that holds the unique user-identifier, commonly this is \'Email\'',
                    ['IdentifierField' => $idfield]
                )
            );
            throw new ValidationException($result);
        }
        if (!isset($data['Password']) || empty($data['Password'])) {
            $result->addError(_t('SilverShop\Checkout\Checkout.PasswordRequired', 'A password is required'));
            throw new ValidationException($result);
        }
        $idval = $data[$idfield];
        if ($member = MemberExtension::get_by_identifier($idval)) {
            // get localized field labels
            $fieldLabels = $member->fieldLabels(false);
            // if a localized value exists, use this for our error-message
            $fieldLabel = isset($fieldLabels[$idfield]) ? $fieldLabels[$idfield] : $idfield;

            $result->addError(
                _t(
                    'SilverShop\Checkout\Checkout.MemberExists',
                    'A member already exists with the {Field} {Identifier}',
                    '',
                    ['Field' => $fieldLabel, 'Identifier' => $idval]
                )
            );
            throw new ValidationException($result);
        }

        /** @var Member $member */
        $member = Member::create()->update($data);
        // 3.2 changed validate to protected which made this fall through the DataExtension and error out
        $validation = $member->doValidate();
        if (!$validation->isValid()) {
            //TODO need to handle i18n here?
            foreach ($validation->getMessages() as $message) {
                $result->addError($message['message']);
            }
        }
        if (!$result->isValid()) {
            throw new ValidationException($result);
        }

        return $member;
    }
}
