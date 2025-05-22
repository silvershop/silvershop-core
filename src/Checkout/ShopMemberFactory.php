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
     * @return Member - new member (not saved to db)
     */
    public function create($data): ?Member
    {
        $validationResult = ValidationResult::create();
        if (!Checkout::member_creation_enabled()) {
            $validationResult->addError(
                _t('SilverShop\Checkout\Checkout.MembershipIsNotAllowed', 'Creating new memberships is not allowed')
            );
            throw ValidationException::create($validationResult);
        }
        $idfield = Config::inst()->get(Member::class, 'unique_identifier_field');
        if (!isset($data[$idfield]) || empty($data[$idfield])) {
            $validationResult->addError(
                _t(
                    'SilverShop\Checkout\Checkout.IdFieldNotFound',
                    'Required field not found: {IdentifierField}',
                    'Identifier is the field that holds the unique user-identifier, commonly this is \'Email\'',
                    ['IdentifierField' => $idfield]
                )
            );
            throw ValidationException::create($validationResult);
        }
        if (!isset($data['Password']) || empty($data['Password'])) {
            $validationResult->addError(_t('SilverShop\Checkout\Checkout.PasswordRequired', 'A password is required'));
            throw ValidationException::create($validationResult);
        }
        $idval = $data[$idfield];
        if (($member = MemberExtension::get_by_identifier($idval)) instanceof Member) {
            // get localized field labels
            $fieldLabels = $member->fieldLabels(false);
            // if a localized value exists, use this for our error-message
            $fieldLabel = isset($fieldLabels[$idfield]) ? $fieldLabels[$idfield] : $idfield;

            $validationResult->addError(
                _t(
                    'SilverShop\Checkout\Checkout.MemberExists',
                    'A member already exists with the {Field} {Identifier}',
                    '',
                    ['Field' => $fieldLabel, 'Identifier' => $idval]
                )
            );
            throw ValidationException::create($validationResult);
        }

        /** @var Member $member */
        $member = Member::create()->update($data);
        // 3.2 changed validate to protected which made this fall through the DataExtension and error out
        $validation = $member->validate();
        if (!$validation->isValid()) {
            //TODO need to handle i18n here?
            foreach ($validation->getMessages() as $message) {
                $validationResult->addError($message['message']);
            }
        }
        if (!$validationResult->isValid()) {
            throw ValidationException::create($validationResult);
        }

        return $member;
    }
}
