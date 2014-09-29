# Migrating from another system

Here is an approach you can use to migrate complex ecommerce data from another system

 * Create a MigrateShopTask that extends MigrationTask
 * Connect to the old database with a different database connection
 * Write functions that create new models from old data
 
 	* Members
 	* Addresses
 	* Categories
 	* Products
 	* Orders
		* OrderItems
		* Shipping
		* Tax
