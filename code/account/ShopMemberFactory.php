<?php

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
     * @return Member|boolean - new member (not saved to db), or false if there is an error.
     */
    public function create($data)
    {
        $result = ValidationResult::create();
        if (!Checkout::member_creation_enabled()) {
            $result->error(
                _t("Checkout.MembershipIsNotAllowed", "Creating new memberships is not allowed")
            );
            throw new ValidationException($result);
        }
        $idfield = Config::inst()->get('Member', 'unique_identifier_field');
        if (!isset($data[$idfield]) || empty($data[$idfield])) {
            $result->error(
                _t(
                    'Checkout.IdFieldNotFound',
                    'Required field not found: {IdentifierField}',
                    'Identifier is the field that holds the unique user-identifier, commonly this is \'Email\'',
                    array('IdentifierField' => $idfield)
                )
            );
            throw new ValidationException($result);
        }
        if (!isset($data['Password']) || empty($data['Password'])) {
            $result->error(_t("Checkout.PasswordRequired", "A password is required"));
            throw new ValidationException($result);
        }
        $idval = $data[$idfield];
        if ($member = ShopMember::get_by_identifier($idval)) {
            // get localized field labels
            $fieldLabels = $member->fieldLabels(false);
            // if a localized value exists, use this for our error-message
            $fieldLabel = isset($fieldLabels[$idfield]) ? $fieldLabels[$idfield] : $idfield;

            $result->error(
                _t(
                    'Checkout.MemberExists',
                    'A member already exists with the {Field} {Identifier}',
                    '',
                    array('Field' => $fieldLabel, 'Identifier' => $idval)
                )
            );
            throw new ValidationException($result);
        }
        $member = Member::create(Convert::raw2sql($data));
        // 3.2 changed validate to protected which made this fall through the DataExtension and error out
        $validation = $member->hasMethod('doValidate') ? $member->doValidate() : $member->validate();
        if (!$validation->valid()) {
            //TODO need to handle i18n here?
            $result->error($validation->message());
        }
        if (!$result->valid()) {
            throw new ValidationException($result);
        }

        return $member;
    }
}
