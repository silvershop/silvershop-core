<?php


/**
 * @description: migrates older versions of e-commerce to the latest one.
 * This has been placed here rather than in the individual classes for the following reasons:
 * - not to clog up individual classes
 * - to get a complete overview in one class
 * - to be able to run parts and older and newer versionw without having to go through several clases to retrieve them
 *
 * @authors: Silverstripe, Jeremy, Nicolaas
 *
 * @package: ecommerce
 * @sub-package: data
 *
 **/




class EcommerceMigration extends DatabaseAdmin {


	function run() {
		$db = DB::getConn();


		//ORDER ITEMS *************************
		if($db->hasTable("Product_OrderItem")) {
			$fieldArray = $db->fieldList("Product_OrderItem");
			$hasField =  isset($fieldArray["ProductVersion"]);
			if($hasField) {
				DB::query("
					UPDATE \"OrderItem\", \"Product_OrderItem\"
						SET \"OrderItem\".\"Version\" = \"Product_OrderItem\".\"ProductVersion\"
					WHERE \"OrderItem\".\"ID\" = \"Product_OrderItem\".\"ID\"
				");
				DB::query("
					UPDATE \"OrderItem\", \"Product_OrderItem\"
						SET \"OrderItem\".\"BuyableID\" = \"Product_OrderItem\".\"ProductID\"
					WHERE \"OrderItem\".\"ID\" = \"Product_OrderItem\".\"ID\"
				");
				DB::query("ALTER TABLE \"Product_OrderItem\" CHANGE COLUMN \"ProductVersion\" \"_obsolete_ProductVersion\" Integer(11)");
				DB::query("ALTER TABLE \"Product_OrderItem\" CHANGE COLUMN \"ProductID\" \"_obsolete_ProductID\" Integer(11)");
				DB::alteration_message('made ProductVersion and ProductID obsolete in Product_OrderItem', 'obsolete');
			}
		}

		//ECOMMERCE PAYMENT *************************
		if(isset($_GET["updatepayment"])) {
			DB::query("
				UPDATE \"Payment\"
				SET \"AmountAmount\" = \"Amount\"
				WHERE
					\"Amount\" > 0
					AND (
						\"AmountAmount\" IS NULL
						OR \"AmountAmount\" = 0
					)
			");
			$countAmountChanges = DB::affectedRows();
			if($countAmountChanges) {
				DB::alteration_message("Updated Payment.Amount field to 2.4 - $countAmountChanges rows updated", "edited");
			}
			DB::query("
				UPDATE \"Payment\"
				SET \"AmountCurrency\" = \"Currency\"
				WHERE
					\"Currency\" <> ''
					AND \"Currency\" IS NOT NULL
					AND (
						\"AmountCurrency\" IS NULL
						OR \"AmountCurrency\" = ''
					)
			");
			$countCurrencyChanges = DB::affectedRows();
			if($countCurrencyChanges) {
				DB::alteration_message("Updated Payment.Currency field to 2.4  - $countCurrencyChanges rows updated", "edited");
			}
			if($countAmountChanges != $countCurrencyChanges) {
				DB::alteration_message("Potential error in Payment fields update to 2.4, please review data", "deleted");
			}
		}



		//ORDER *************************

		// 1) If some orders with the old structure exist (hasShippingCost, Shipping and AddedTax columns presents in Order table), create the Order Modifiers SimpleShippingModifier and TaxModifier and associate them to the order

		// we must check for individual database types here because each deals with schema in a none standard way
		$fieldArray = $db->fieldList("Order");
		$hasField =  isset($fieldArray["Shipping"]);
 		if($hasField) {
 			if($orders = DataObject::get('Order')) {
 				foreach($orders as $order) {
 					$id = $order->ID;
 					$hasShippingCost = DB::query("SELECT \"hasShippingCost\" FROM \"Order\" WHERE \"ID\" = '$id'")->value();
 					$shipping = DB::query("SELECT \"Shipping\" FROM \"Order\" WHERE \"ID\" = '$id'")->value();
 					$addedTax = DB::query("SELECT \"AddedTax\" FROM \"Order\" WHERE \"ID\" = '$id'")->value();
					$country = $order->findShippingCountry(true);
 					if($hasShippingCost == '1' && $shipping != null) {
 						$modifier1 = new SimpleShippingModifier();
 						$modifier1->Amount = $shipping < 0 ? abs($shipping) : $shipping;
 						$modifier1->Type = 'Chargeable';
 						$modifier1->OrderID = $id;
 						$modifier1->Country = $country;
 						$modifier1->ShippingChargeType = 'Default';
 						$modifier1->write();
 					}
 					if($addedTax != null) {
 						$modifier2 = new TaxModifier();
 						$modifier2->Amount = $addedTax < 0 ? abs($addedTax) : $addedTax;
 						$modifier2->OrderID = $id;
 						$modifier2->Country = $country;
 						$modifier2->Name = 'Undefined After Ecommerce Upgrade';
 						$modifier2->TaxType = 'Exclusive';
 						$modifier2->write();
 					}
 				}
 				DB::alteration_message('The \'SimpleShippingModifier\' and \'TaxModifier\' objects have been successfully created and linked to the appropriate orders present in the \'Order\' table', 'created');
 			}
 			DB::query("ALTER TABLE \"Order\" CHANGE COLUMN \"hasShippingCost\" \"_obsolete_hasShippingCost\" tinyint(1)");
 			DB::query("ALTER TABLE \"Order\" CHANGE COLUMN \"Shipping\" \"_obsolete_Shipping\" decimal(9,2)");
 			DB::query("ALTER TABLE \"Order\" CHANGE COLUMN \"AddedTax\" \"_obsolete_AddedTax\" decimal(9,2)");
 			DB::alteration_message('The columns \'hasShippingCost\', \'Shipping\' and \'AddedTax\' of the table \'Order\' have been renamed successfully. Also, the columns have been renamed respectly to \'_obsolete_hasShippingCost\', \'_obsolete_Shipping\' and \'_obsolete_AddedTax\'', 'obsolete');
		}


		//set starting order number ID
		$number = intval(Order::get_order_id_start_number());
		$currentMax = 0;
		//set order ID
		if($number) {
			$count = DB::query("SELECT COUNT( \"ID\" ) FROM \"Order\" ")->value();
		 	if($count > 0) {
				$currentMax = DB::Query("SELECT MAX( \"ID\" ) FROM \"Order\"")->value();
			}
			if($number > $currentMax) {
				DB::query("ALTER TABLE \"Order\"  AUTO_INCREMENT = $number ROW_FORMAT = DYNAMIC ");
				DB::alteration_message("Change OrderID start number to ".$number, "edited");
			}
		}
		//fix bad status
		$dos = self::get_order_status_options();
		if($dos) {
			$firstOption = $dos->First();
			$badOrders = DataObject::get("Order", "\"StatusID\" = 0 OR \"StatusID\" IS NULL");
			if($badOrders && $firstOption) {
				foreach($badOrders as $order) {
					$order->StatusID = $firstOption->ID;
					$order->write();
					DB::alteration_message("No order status for order number #".$order->ID." reverting to: $firstOption->Name.","error");
				}
			}
		}
		$db = DB::getConn();
		$fieldArray = $db->fieldList("Order");
		$hasField =  isset($fieldArray["ShippingAddress"]);
		if($hasField) {
 			if($orders = DataObject::get('Order', "\"UseShippingAddress\" = 1  OR (\"ShippingName\" IS NOT NULL AND \"ShippingName\" <> '')")) {
 				foreach($orders as $order) {
					$obj = new ShippingAddress();
					if(isset($order->ShippingName)) {$obj->ShippingName = $order->ShippingName;}
					if(isset($order->ShippingAddress)) {$obj->ShippingAddress = $order->ShippingAddress;}
					if(isset($order->ShippingAddress2)) {$obj->ShippingAddress2 = $order->ShippingAddress2;}
					if(isset($order->ShippingCity)) {$obj->ShippingCity = $order->ShippingCity;}
					if(isset($order->ShippingPostalCode)) {$obj->ShippingPostalCode = $order->ShippingPostalCode;}
					if(isset($order->ShippingState)) {$obj->ShippingState = $order->ShippingState;}
					if(isset($order->ShippingCountry)) {$obj->ShippingCountry = $order->ShippingCountry;}
					if(isset($order->ShippingPhone)) {$obj->ShippingPhone = $order->ShippingPhone;}
					$obj->OrderID = $order->ID;
					$obj->write();
					$order->ShippingAddressID = $obj->ID;
					$order->write();
				}
			}

			if( $db instanceof PostgreSQLDatabase ){
				@DB::query('ALTER TABLE "Order" RENAME "ShippingName"  TO "_obsolete_ShippingName"');
				@DB::query('ALTER TABLE "Order" ALTER "_obsolete_ShippingName" TYPE character varying(255)');

				@DB::query('ALTER TABLE "Order" RENAME "ShippingAddress"  TO "_obsolete_ShippingAddress"');
				@DB::query('ALTER TABLE "Order" ALTER "_obsolete_ShippingAddress" TYPE character varying(255)');

				@DB::query('ALTER TABLE "Order" RENAME "ShippingAddress2"  TO "_obsolete_ShippingAddress2"');
				@DB::query('ALTER TABLE "Order" ALTER "_obsolete_ShippingAddress2" TYPE character varying(255)');

				@DB::query('ALTER TABLE "Order" RENAME "ShippingCity"  TO "_obsolete_ShippingCity"');
				@DB::query('ALTER TABLE "Order" ALTER "_obsolete_ShippingCity" TYPE character varying(255)');

				@DB::query('ALTER TABLE "Order" RENAME "ShippingPostalCode"  TO "_obsolete_ShippingPostalCode"');
				@DB::query('ALTER TABLE "Order" ALTER "_obsolete_ShippingPostalCode" TYPE character varying(255)');

				@DB::query('ALTER TABLE "Order" RENAME "ShippingState"  TO "_obsolete_ShippingState"');
				@DB::query('ALTER TABLE "Order" ALTER "_obsolete_ShippingState" TYPE character varying(255)');

				@DB::query('ALTER TABLE "Order" RENAME "ShippingCountry"  TO "_obsolete_ShippingCountry"');
				@DB::query('ALTER TABLE "Order" ALTER "_obsolete_ShippingCountry" TYPE character varying(255)');

				@DB::query('ALTER TABLE "Order" RENAME "ShippingPhone"  TO "_obsolete_ShippingPhone"');
				@DB::query('ALTER TABLE "Order" ALTER "_obsolete_ShippingPhone" TYPE character varying(255)');
			}
			else
			{
	 			@DB::query("ALTER TABLE \"Order\" CHANGE COLUMN \"ShippingName\" \"_obsolete_ShippingName\" Varchar(255)");
	 			@DB::query("ALTER TABLE \"Order\" CHANGE COLUMN \"ShippingAddress\" \"_obsolete_ShippingAddress\" Varchar(255)");
	 			@DB::query("ALTER TABLE \"Order\" CHANGE COLUMN \"ShippingAddress2\" \"_obsolete_ShippingAddress2\" Varchar(255)");
	 			@DB::query("ALTER TABLE \"Order\" CHANGE COLUMN \"ShippingCity\" \"_obsolete_ShippingCity\" Varchar(255)");
	 			@DB::query("ALTER TABLE \"Order\" CHANGE COLUMN \"ShippingPostalCode\" \"_obsolete_ShippingPostalCode\" Varchar(255)");
	 			@DB::query("ALTER TABLE \"Order\" CHANGE COLUMN \"ShippingState\" \"_obsolete_ShippingState\" Varchar(255)");
	 			@DB::query("ALTER TABLE \"Order\" CHANGE COLUMN \"ShippingCountry\" \"_obsolete_ShippingCountry\" Varchar(255)");
	 			@DB::query("ALTER TABLE \"Order\" CHANGE COLUMN \"ShippingPhone\" \"_obsolete_ShippingPhone\" Varchar(255)");
			}
		}
		//move to ShippingAddress
		$db = DB::getConn();
		if( $db instanceof PostgreSQLDatabase ){
      $statusFieldExists = DB::query("SELECT column_name FROM information_schema.columns WHERE table_name ='Order' AND column_name = 'Status'")->numRecords();
		}
		else{
			// default is MySQL - broken for others, each database conn type supported must be checked for!
      $statusFieldExists = DB::query("SHOW COLUMNS FROM \"Order\" LIKE 'Status'")->numRecords();
		}
		if($statusFieldExists) {
		// 2) Cancel status update
			$orders = DataObject::get('Order', "\"Status\" = 'Cancelled'");
			$admin = Member::currentMember();
			if($orders && $admin) {
				foreach($orders as $order) {
					$order->CancelledByID = $admin->ID;
					$order->write();
				}
				DB::alteration_message('The orders which status was \'Cancelled\' have been successfully changed to the status \'AdminCancelled\'', 'changed');
			}
			$rows = DB::query("SELECT \"ID\", \"Status\" FROM \"Order\"");
			if($rows) {
				$CartObject = null;
				$UnpaidObject = null;
				$PaidObject = null;
				$SentObject = null;
				$AdminCancelledObject = null;
				$MemberCancelledObject = null;
 				foreach($rows as $row) {
					switch($row["Status"]) {
						case "Cart":
							if(!$CartObject) {
								if(!($CartObject = DataObject::get_one("OrderStep", "\"Code\" = 'CREATED'"))) {
									singleton('OrderStep')->requireDefaultRecords();
								}
							}
							if($CartObject = DataObject::get_one("OrderStep", "\"Code\" = 'CREATED'")) {
								DB::query("UPDATE \"Order\" SET \"StatusID\" = ".$CartObject->ID." WHERE \"Order\".\"ID\" = ".$row["ID"]);
							}
							break;
						case "Query":
						case "Unpaid":
							if(!$UnpaidObject) {
								if(!($UnpaidObject = DataObject::get_one("OrderStep", "\"Code\" = 'SUBMITTED'"))) {
									singleton('OrderStep')->requireDefaultRecords();
								}
							}
							if($UnpaidObject = DataObject::get_one("OrderStep", "\"Code\" = 'SUBMITTED'")) {
								DB::query("UPDATE \"Order\" SET \"StatusID\" = ".$UnpaidObject->ID." WHERE \"Order\".\"ID\" = ".$row["ID"]);
							}

							break;
						case "Processing":
						case "Paid":
							if(!$PaidObject) {
								if(!($PaidObject = DataObject::get_one("OrderStep", "\"Code\" = 'PAID'"))) {
									singleton('OrderStep')->requireDefaultRecords();
								}
							}
							if($PaidObject = DataObject::get_one("OrderStep", "\"Code\" = 'PAID'")) {
								DB::query("UPDATE \"Order\" SET \"StatusID\" = ".$PaidObject->ID." WHERE \"Order\".\"ID\" = ".$row["ID"]);
							}
							break;
						case "Sent":
						case "Complete":
							if(!$PaidObject) {
								if(!($SentObject = DataObject::get_one("OrderStep", "\"Code\" = 'SENT'"))) {
									singleton('OrderStep')->requireDefaultRecords();
								}
							}
							if($SentObject = DataObject::get_one("OrderStep", "\"Code\" = 'SENT'")) {
								DB::query("UPDATE \"Order\" SET \"StatusID\" = ".$SentObject->ID." WHERE \"Order\".\"ID\" = ".$row["ID"]);
							}
							break;
						case "AdminCancelled":
							if(!$AdminCancelledObject) {
								if(!($AdminCancelledObject  = DataObject::get_one("OrderStep", "\"Code\" = 'SENT'"))) {
									singleton('OrderStep')->requireDefaultRecords();
								}
							}
							if(!$adminID) {
								$adminID = Member::currentUserID();
								if(!$adminID) {
									$adminID = 1;
								}
							}
							DB::query("UPDATE \"Order\" SET \"StatusID\" = ".$AdminCancelledObject->ID." WHERE \"Order\".\"ID\" = ".$row["ID"].", \"CancelledByID\" = ".$adminID);
							break;
						case "MemberCancelled":
							if(!$MemberCancelledObject) {
								if(!($MemberCancelledObject = DataObject::get_one("OrderStep", "\"Code\" = 'SENT'"))) {
									singleton('OrderStep')->requireDefaultRecords();
								}
							}
							DB::query("UPDATE \"Order\" SET \"StatusID\" = ".$MemberCancelledObject->ID.", \"CancelledByID\" = \"MemberID\" WHERE \"Order\".\"ID\" = '".$row["ID"]."'");
							break;
					}
				}
			}
			if( $db instanceof PostgreSQLDatabase ) {
				@DB::query('ALTER TABLE "Order" RENAME "Status"  TO "_obsolete_Status"');
				@DB::query('ALTER TABLE "Order" ALTER "_obsolete_Status" TYPE character varying(255)');
			}
			else {
			 	@DB::query("ALTER TABLE \"Order\" CHANGE COLUMN \"Status\" \"_obsolete_Status\" Varchar(255)");
			}
		}


		// ORDER ITEM *************************
		// we must check for individual database types here because each deals with schema in a none standard way
		//can we use Table::has_field ???
		$db = DB::getConn();
		$fieldArray = $db->fieldList("OrderItem");
		$hasField =  isset($fieldArray["ItemID"]);
		if($hasField) {
			DB::query("UPDATE \"OrderItem\" SET \"OrderItem\".\"BuyableID\" = \"OrderItem\".\"ItemID\"");
 			DB::query("ALTER TABLE \"OrderItem\" CHANGE COLUMN \"ItemID\" \"_obsolete_ItemID\" Integer(11)");
 			DB::alteration_message('Moved ItemID to BuyableID in OrderItem', 'obsolete');
		}


	}


}
