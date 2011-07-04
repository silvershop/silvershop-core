# Loading and modifying products via SpreadSheet

Often a webstore will sell many products, perhaps already stored in a different system.

Products can be created and updated by uploading a CSV spreadsheet. This is done in the Products section of the CMS.
Make sure "Product" is selected on the left tab, then use the import form at the bottom to load your product spreadsheet.

## General Info

Before uploading your spreadsheet, you will need to ensure that you have set up the correctly named columns.

ProductID/SKU must be unique, otherwise duplicates will be overridden. If there is no ProductID, Title will be used instead.

If a column title matches a field on Product, it will be loaded in. eg if you specify a "AllowPurchase" column, the values will be loaded into each Product's AllowPurchase field. Clicking the "Show Specification for Product" link in the Products section of the CMS will help you identify the possible fields to import to.

You can find a test spreadsheet in ecommerce/tests/test_products.csv , which was exported from ecommerce/tests/test_products.xls.

## Assigning products to Categories

If you specify a 'Category' or 'ProductGroup' column, the loader will look for a ProductGroup with a title matching the specified category name. If you desire to use this system, it is recommended building your ProductGroup structure before loading the products.


## Product Images

Specifying a filename in a 'Image' or 'Photo' will link the product up to an image with the same filename that has been previously uploaded. This means you need to upload the product photos somewhere in the assets folder structure before doing the product bulk load.

## Product Variations

There are 6 column names dedicated to variations: 'Variation1' - 'Variation6'. Each cell should use the following format: VariationType: value,value,value

eg: Size:extra small,small,medium,large,extra large

Note: be careful how you name variation types, because all of the variation values will be assigned to common variation types. Eg: you could mistakingly end up with the following: Size:extra small,small,medium,large,extra large,10,11,12,13,14
Trouble Shooting Tips

Make sure you don't have rouge spaces in your headings.


## Settings before you load

Note that you can have your developer choose some settings before you load the spreadsheet.

 * create new product categories on the fly
 * force every product to become part of a particular category.
