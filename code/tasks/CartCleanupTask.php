<?php
/**
 * Cart Cleanup Task
 * Removes all orders (carts) that are older than a specific number of days.
 * @package shop
 * @subpackage tasks
 */
class CartCleanupTask extends BuildTask{

	public static $batch_size = 500;
	protected static $cleardays = 90;

	protected $title = "Delete Old Carts";
	protected $description = "Deletes carts that are older than a certian number of days (default: 90).
									Add type=sql to use a faster, but less safe query.";

	public function set_clear_days($days = 90){
		self::$cleardays = $days;
	}

	//Find and remove carts older than X days
	public function run($request){
		if(strtolower($request->getVar('type')) == 'sql'){
			$this->sqldelete();
		}else{
			$this->ormdelete();
		}
	}

	/**
	 * Perform delete via SQL commands, bypassing PHP.
	 * Fast, but may miss some custom cleanup or may delete records linked to other data.
	 */
	public function sqldelete(){
		//delete carts older than 90 days
		$days = self::$cleardays;
		$filter = ($days) ? "AND \"Order\".\"LastEdited\" < ADDDATE(NOW(),INTERVAL -$days DAY)" : "";
		$deleteorders = new SQLQuery("*","\"Order\"","\"Order\".\"Status\" = 'Cart' $filter");
		$deleteorders->delete = true;
		$result = $deleteorders->execute();
		echo "Deleted ".(int)$result->numRecords()." Order.\n<br/>";

		//delete orphaned attributes
		$attributeclass = "OrderAttribute";
		$join = "LEFT JOIN \"Order\" ON \"OrderAttribute\".\"OrderID\" = \"Order\".\"ID\"";
		$where = "\"Order\".\"ID\" IS NULL";
		$from = "\"OrderAttribute\" $join";
		$result = DB::query("DELETE $attributeclass FROM $from WHERE $where"); //Can't use SQLQuery for joined deletes, because of a bug with SQLQuery
		echo "Deleted ".(int)$result->numRecords()." $attributeclass.\n<br/>";

		//delete orphaned subclasses of OrderAttribute
		foreach(ClassInfo::dataClassesFor($attributeclass) as $dataclass){
			if(!$dataclass || $dataclass == $attributeclass) continue;
			$from = "\"$dataclass\" LEFT JOIN \"$attributeclass\" ON \"$dataclass\".\"ID\" = \"$attributeclass\".\"ID\"";
			$where = "\"$attributeclass\".\"ID\" IS NULL";
			$result = DB::query("DELETE $dataclass FROM $from WHERE $where"); //Can't use SQLQuery for joined deletes, because of a bug with SQLQuery
		 	echo "Deleted ".(int)$result->numRecords()." $dataclass.\n<br/>";
		}
	}

	/**
	 * Delete via standard ORM delete functions.
	 * This will trigger any onBeforeDelete or onAfterDelete calls.
	 */
	public function ormdelete(){
		$start = 0;
		$count = 0;
		$time = date('Y-m-d H:i:s', strtotime("-".self::$cleardays." days"));
		$filter = "\"Status\" = 'Cart'";
		if(self::$cleardays){
			$filter .= " AND \"LastEdited\" < '$time'";
		}
		while($batch = DataObject::get('Order',$filter,"\"Created\" ASC","",$start.",".self::$batch_size)){
			foreach($batch as $cart){
				echo ". ";
				$cart->delete();
				$cart->destroy();
				$count++;
			}
			$start += self::$batch_size;
			echo "$count old carts removed.\n<br/>";
		};
	}



}
