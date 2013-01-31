<?php
/**
 * Updates database to work with latest version of the code.
 */
class ShopMigrationTask extends MigrationTask{
	
	/**
	 * Choose how many orders get processed at a time.
	 */
	static $batch_size = 250;
	
	protected $title = "Migrate Shop";
	protected $description = "Where dev/build is not enough, this task updates database to work with latest version of shop module.
		You may want to run the CartCleanupTask before migrating if you want to discard past carts.";
	
	/**
	 * Migrate upwards
	 */
	function up(){
		$start = 0;
		$count = 0;
		//batch process orders
		while($batch = DataObject::get('Order',"","\"Created\" ASC","",$start.",".self::$batch_size)){
			foreach($batch as $order){
				$this->migrate($order);
				echo ". ";
				$count++;
			}
			$start += self::$batch_size;
			echo "$count orders updated.\n<br/>";
		};
		$this->migrateProductPrice();
		$this->migrateProductVariationsAttribues();
		
		//TODO: migrate CheckoutPage->TermsPageID to ShopConfig
	}
	
	/**
	 * Perform migration scripts on a single order.
	 */
	function migrate($order){
		//TODO: set a from / to version to preform a migration with
		$this->migrateStatuses($order);
		$this->migrateMemberFields($order);
		$this->migrateShippingValues($order);
		$this->migrateOrderCalculation($order);
		$order->write();
	}
	
	function migrateProductPrice(){
		$db = DB::getConn();
		//if BasePrice has no values, but Price does, then copy from Price
		if($db->hasTable("Product") && !DataObject::get_one("Product","\"BasePrice\" > 0")){
			//TODO: warn against lost data
			DB::query("UPDATE \"Product\" SET \"BasePrice\" = \"Price\";");
			DB::query("UPDATE \"Product_Live\" SET \"BasePrice\" = \"Price\";");
			//TODO: rename, if possible without breaking migraton task next time it runs
			//$db->renameField("Product","Price","Price_obselete");
			//$db->renameField("Product_Live","Price","Price_obselete");
		}
	}
	
	/**
	 * Customer and shipping details have been added to Order,
	 * so that memberless (guest) orders can be placed.
	 */
	function migrateMemberFields($order){
		if($member = $order->Member()){
			$fieldstocopy = array(
				'FirstName',
				'Surname',
				'Email',
				'Address',
				'AddressLine2',
				'City',
				'Country',
				'HomePhone',
				'MobilePhone',
				'Notes'
			);
			foreach($fieldstocopy as $field){
				if(!$order->$field){
					$order->$field = $member->$field;
				}
			}
		}
	}
	
	/**
	 * Migrate old statuses
	 */
	function migrateStatuses($order){
		switch($order->Status){
			case "Cancelled": //Pre version 0.5
				$order->Status = 'AdminCancelled';
				break;
			case "":
				$order->Status = 'Cart';
				break;
		}
	}
	
	/**
	 * Convert shipping and tax columns into modifiers
	 * 
	 * Applies to pre 0.6 sites
	 */
	function migrateShippingValues($order){
		//TODO: see if this actually works..it probably needs to be writeen to a SQL query
		$country = $order->findShippingCountry(true);
		if($order->hasShippingCost && abs($order->Shipping)){
			$modifier1 = new ShippingModifier();
			$modifier1->Amount = $order->Shipping < 0 ? abs($order->Shipping) : $order->Shipping;
			$modifier1->Type = 'Chargable';
			$modifier1->OrderID = $order->ID;
			$modifier1->Country = $country;
			$modifier1->ShippingChargeType = 'Default';
			$modifier1->write();
			$order->hasShippingCost = null;
			$order->Shipping = null;
		}
		if($order->AddedTax) {
			$modifier2 = new TaxModifier();
			$modifier2->Amount = $order->AddedTax < 0 ? abs($order->AddedTax) : $order->AddedTax;
			$modifier2->Type = 'Chargable';
			$modifier2->OrderID = $order->ID;
			$modifier2->Country = $country;
			//$modifier2->Name = 'Undefined After Ecommerce Upgrade';
			$modifier2->TaxType = 'Exclusive';
			$modifier2->write();
			$order->AddedTax = null;
		}
	}
	
	/**
	 * Performs calculation function on un-calculated orders.
	 */
	function migrateOrderCalculation($order){
		if(!is_numeric($order->Total) || $order->Total <= 0){
			$order->calculate();
			$order->write();
		}
	}
	
	function migrateProductVariationsAttribues(){
		$db = DB::getConn();
		//TODO: delete Product_VariationAttribute, if it's empty
		if($db->hasTable("Product_VariationAttributes")){ //TODO: check if Product_VariationAttributeTypes table is empty
			DB::query("DROP TABLE \"Product_VariationAttributeTypes\"");
			$db->renameTable("Product_VariationAttributes","Product_VariationAttributeTypes");
		}
	}
	
	function migrateShippingTaxValues(){
		//rename obselete columns
		//DB::query("ALTER TABLE \"Order\" CHANGE COLUMN \"hasShippingCost\" \"_obsolete_hasShippingCost\" tinyint(1)");
		//DB::query("ALTER TABLE \"Order\" CHANGE COLUMN \"Shipping\" \"_obsolete_Shipping\" decimal(9,2)");
		//DB::query("ALTER TABLE \"Order\" CHANGE COLUMN \"AddedTax\" \"_obsolete_AddedTax\" decimal(9,2)");
	}
	
}