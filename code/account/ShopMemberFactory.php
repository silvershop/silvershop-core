<?php

class ShopMemberFactory{

	/**
	 * Create member account from data array.
	 * Data must contain unique identifier.
	 *
	 * @throws ValidationException
	 * @param $data - map of member data
	 * @return Member|boolean - new member (not saved to db), or false if there is an error.
	 */
	public function create($data) {
		$result = new ValidationResult();
		if(!Checkout::member_creation_enabled()) {
			$result->error(
				_t("Checkout.MEMBERSHIPSNOTALLOWED", "Creating new memberships is not allowed")
			);
			throw new ValidationException($result);
		}
		$idfield = Config::inst()->get('Member', 'unique_identifier_field');
		if(!isset($data[$idfield]) || empty( $data[$idfield])){
			$result->error(
				sprintf(_t("Checkout.IDFIELDNOTFOUND", "Required field not found: %s"), $idfield)
			);
			throw new ValidationException($result);
		}
		if(!isset($data['Password']) || empty($data['Password'])){
			$result->error(_t("Checkout.PASSWORDREQUIRED", "A password is required"));
			throw new ValidationException($result);
		}
		$idval = $data[$idfield];
		if(ShopMember::get_by_identifier($idval)){
			$result->error(sprintf(
				_t("Checkout.MEMBEREXISTS", "A member already exists with the %s %s"),
				_t("Member.".$idfield, $idfield),
				$idval
			));
			throw new ValidationException($result);
		}
		$member = new Member(Convert::raw2sql($data));
		// 3.2 changed validate to protected which made this fall through the DataExtension and error out
		$validation = $member->hasMethod('doValidate') ? $member->doValidate() : $member->validate();
		if(!$validation->valid()){
			//TODO need to handle i18n here?
			$result->error($validation->message());
		}
		if(!$result->valid()){
			throw new ValidationException($result);
		}

		return $member;
	}

}
