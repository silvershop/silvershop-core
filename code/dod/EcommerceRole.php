<?php
/**
 * @description EcommerceRole provides customisations to the {@link Member}
 * class specifically for this ecommerce module.
 *
 *
 * @authors: Silverstripe, Jeremy, Nicolaas
 *
 * @package ecommerce
 * @sub-package member
 *
 **/

class EcommerceRole extends DataObjectDecorator {


	protected static $automatic_membership = true;
		static function set_automatic_membership($b){self::$automatic_membership = $b;}
		static function get_automatic_membership(){return self::$automatic_membership;}


	function extraStatics() {
		return array(
			'db' => array(
				'Notes' => 'HTMLText'
			),
			'has_many' => array(
				"Orders" => "Order"
			),
			'casting' => array(
				"FullCountryName" => "Varchar"
			)
		);
	}


	protected static $customer_group_code = 'shop_customers';
		static function set_customer_group_code(string $s) {self::$customer_group_code = $s;}
		static function get_customer_group_code() {return self::$customer_group_code;}

	protected static $customer_group_name = "shop customers";
		static function set_customer_group_name(string $s) {self::$customer_group_name = $s;}
		static function get_customer_group_name() {return self::$customer_group_name;}

	protected static $customer_permission_code = "SHOP_CUSTOMER";
		static function set_customer_permission_code(string $s) {self::$customer_permission_code = $s;}
		static function get_customer_permission_code() {return self::$customer_permission_code;}


	/**
	 *@return DataObject (Group)
	 **/
	public function get_customer_group() {
		return DataObject::get_one("Group", "\"Code\" = '".self::get_customer_group_code()."' OR \"Title\" = '".self::get_customer_group_name()."'");
	}

/*******************************************************
   * SHOP ADMIN
*******************************************************/


	protected static $admin_group_code = "shop_administrators";
		static function set_admin_group_code(string $s) {self::$admin_group_code = $s;}
		static function get_admin_group_code() {return self::$admin_group_code;}

	protected static $admin_group_name = "shop administrators";
		static function set_admin_group_name(string $s) {self::$admin_group_name = $s;}
		static function get_admin_group_name() {return self::$admin_group_name;}

	protected static $admin_permission_code = "SHOP_ADMIN";
		static function set_admin_permission_code(string $s) {self::$admin_permission_code = $s;}
		static function get_admin_permission_code() {return self::$admin_permission_code;}

	protected static function add_members_to_customer_group() {
		$gp = DataObject::get_one("Group", "\"Title\" = '".self::get_customer_group_name()."'");
		if($gp) {
			$allCombos = DB::query("
				SELECT \"Group_Members\".\"ID\", \"Group_Members\".\"MemberID\", \"Group_Members\".\"GroupID\"
				FROM \"Group_Members\"
				WHERE \"Group_Members\".\"GroupID\" = ".$gp->ID.";"
			);
			//make an array of all combos
			$alreadyAdded = array();
			$alreadyAdded[-1] = -1;
			if($allCombos) {
				foreach($allCombos as $combo) {
					$alreadyAdded[$combo["MemberID"]] = $combo["MemberID"];
				}
			}
			$unlistedMembers = DataObject::get(
				"Member",
				$where = "\"Member\".\"ID\" NOT IN (".implode(",",$alreadyAdded).")",
				$sort = "",
				$join = "INNER JOIN \"Order\" ON \"Order\".\"MemberID\" = \"Member\".\"ID\""
			);

			//add combos
			if($unlistedMembers) {
				$existingMembers = $gp->Members();
				foreach($unlistedMembers as $member) {
					$existingMembers->add($member);
				}
			}
		}
	}

	/**
	 * Create a new member with given data for a new member,
	 * or merge the data into the logged in member.
	 *
	 * IMPORTANT: Before creating a new Member record, we first
	 * check that the request email address doesn't already exist.
	 *
	 * @param array $data Form request data to update the member with
	 * @return boolean|object Member object or boolean FALSE
	 */
	public static function createOrMerge($data) {
		user_error("depreciated, please use EcommerceRole::ecommerce_create_or_merge", E_USER_NOTICE);
		return self::ecommerce_create_or_merge($data);
	}

	/**
	 * OPTIONS:
	 * ** NO PROBLEM:
	 * 1. user not logged in: create a new member
	 * 2. user logged in: some information might be different (and is updated)
	 * ** PROBLEM
	 * 3. user not logged in, but (s)he enters an email of an existing user... - as part of our goals under Ecommerce, we want a user to be able to do this...
	 * 4. user logged in and changes email to another email belonging to another registered user
	 *
	 * NOTE: ecommerce_create_or_merge return false if logged  in user is changing their email address to that of another user
	 * it return true if the user is not logged in, but the email address is listed as a user, and it
	 * return the member object if (a) the user is logged in and email address matches or (b) we can create a new user with the email address supplied.
	 * The rational behind the return values is that FALSE: real problem: TRUE: not really a problem but we want to make sure not to return the member,
	 * as no changes should be made to the member (since the user is not logged in).
	 * Is it a problem that a not-logged-in user can place an order under the "name" of a logged-in user?
	 * Not really, as doing so does not give them access to the account.  However, it might be worthwhile to note that they have done so without logging in .
	 *
	 * NOTE: Because we are using a ConfirmedPasswordField, the password will be an array of two fields
	 *
	 * @return DataObject|FALSE |TRUE = see explanation above
	 *
	 **/
	public static function ecommerce_create_or_merge($data, $testOnly = false) {
		//
		// SEE issue 142
		$uniqueField = Member::get_unique_identifier_field();

		//The check below covers both Scenario 3 and 4....
		if(isset($data[$uniqueField])) {
			$uniqueFieldData = Convert::raw2xml($data[$uniqueField]);
			$existingUniqueMember = DataObject::get_one('Member', "\"$uniqueField\" = '{$uniqueFieldData}'");
			if($existingUniqueMember && $existingUniqueMember->exists()) {
				if(Member::currentUserID() != $existingUniqueMember->ID) {
					if(Member::currentUserID) {
						return false;
					}
					else {
						//NOTE: we do not return the existing member, because the user is not logged in and therefore the user can not be changed.
						//in some cases this may result in out-of-date data.
						return true;
					}
				}
			}
		}
		if(!$member = Member::currentUser()) {
			$member = new Member();
		}
		return $member;
	}



	/**
	 *
	 * @return FieldSet
	 */
	function getEcommerceFields() {
		$fields = new FieldSet(
			new HeaderField(_t('EcommerceRole.PERSONALINFORMATION','Personal Information'), 3),
			new TextField('FirstName', _t('EcommerceRole.FIRSTNAME','First Name')),
			new TextField('Surname', _t('EcommerceRole.SURNAME','Surname')),
			new EmailField('Email', _t('EcommerceRole.EMAIL','Email'))
		);
		$this->owner->extend('augmentEcommerceFields', $fields);
		return $fields;
	}

	/**
	 * Return which member fields should be required on {@link OrderForm}
	 * and {@link ShopAccountForm}.
	 *
	 * @return array
	 */
	function getEcommerceRequiredFields() {
		$fields = array(
			'FirstName',
			'Surname',
			'Email'
		);
		$this->owner->extend('augmentEcommerceRequiredFields', $fields);
		return $fields;
	}

	/**
	 * get CMS fields describing the member in the CMS when viewing the order.
	 *
	 * @return Field / ComponentSet
	 **/

	public function getEcommerceFieldsForCMS() {
		$fields = new CompositeField();
		$memberTitle = new TextField("MemberTitle", "Name", $this->getTitle());
		$fields->push($memberTitle->performReadonlyTransformation());
		$memberEmail = new TextField("MemberEmail","Email", $this->Email);
		$fields->push($memberEmail->performReadonlyTransformation());
		$lastLogin = new TextField("MemberLastLogin","Last login",$this->dbObject('LastVisited')->Nice());
		$fields->push($lastLogin->performReadonlyTransformation());
		if($group = EcommerceRole::get_customer_group()) {
			$fields->push(new LiteralField("EditMembers", '<p><a href="/admin/security/show/'.$group->ID.'/">view (and edit) all customers</a></p>'));
		}
		return $fields;
	}

	/**
	 *@return String (Country Name) e.g. Switzerland
	 **/
	public function CountryTitle() {
		return self::find_country_title($this->owner->Country);
	}

	//this method needs to be tested!
	public function onAfterWrite() {
		parent::onAfterWrite();
		self::add_members_to_customer_group();
	}

	public function requireDefaultRecords() {
		parent::requireDefaultRecords();
		if(!$admin_group = DataObject::get_one("Group", "\"Code\" = '".self::get_admin_group_code()."'")) {
			$admin_group = new Group();
			$admin_group->Code = self::get_admin_group_code();
			$admin_group->Title = self::get_admin_group_name();
			$admin_group->write();
			Permission::grant( $admin_group->ID, self::get_admin_permission_code());
			DB::alteration_message(self::get_admin_group_name().' Group created',"created");
		}
		elseif(DB::query("SELECT * FROM \"Permission\" WHERE \"GroupID\" = '".$admin_group->ID."' AND \"Code\" LIKE '".self::get_admin_permission_code()."'")->numRecords() == 0 ) {
			Permission::grant($admin_group->ID, self::get_admin_permission_code());
			DB::alteration_message(self::get_admin_group_name().' permissions granted',"created");
		}
		if(!$customer_group = DataObject::get_one("Group", "\"Code\" = '".self::get_customer_group_code()."'")) {
			$customer_group = new Group();
			$customer_group->Code = self::get_customer_group_code();
			$customer_group->Title = self::get_customer_group_name();
			$customer_group->write();
			Permission::grant( $customer_group->ID, self::get_customer_permission_code());
			DB::alteration_message(self::get_customer_group_name().' Group created',"created");
		}
		elseif(DB::query("SELECT * FROM \"Permission\" WHERE \"GroupID\" = '".$customer_group->ID."' AND \"Code\" LIKE '".self::get_customer_permission_code()."'")->numRecords() == 0 ) {
			Permission::grant($customer_group->ID, self::get_customer_permission_code());
			DB::alteration_message(self::get_customer_group_name().' permissions granted',"created");
		}
	}

	/**
	 *@return Boolean
	 **/
	function IsShopAdmin() {
		if($this->owner->IsAdmin()) {
			return true;
		}
		else{
			return Permission::checkMember($this->owner, self::get_admin_permission_code());
		}
	}

	function populateDefaults() {
		parent::populateDefaults();
		$this->Country = EcommerceCountry::get_country();
	}

}



