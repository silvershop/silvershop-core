Product Variations
==================

With the eCommerce module is possible to sell variants of the same product. Doing so keeps your
store looking tidy when users browse the site. Here we explain how you can manage these variations.

A product can have an unlimited number of variations. Each variation has a individual price and
product id associated with it.

A few terms to understand: _Variation, Attribute Type, Attribute Value._
This table for a "Ball" product explains the terminology visually:

![Here is an example table to help demonstrate](\images\product-variation-table.jpg)

Each variation also has one or more attribute values of a specific type.
The ball example above has a variation with the attribute value 'small' for the attribute type 'size'.


Creating Attribute Types and Values
-----------------------------------

Before you create a product variation, you should first create a some attributes types, with values
to choose from.

 1. Create Attribute Types
 
  * In the CMS, select the product you wish to edit.
  * Find the 'Content > Variations' tab for your product. 
  * Click the "Add Product Attribute Type" link in the "Variation Attribute Types" table.
  * Give the new attribute type a name (eg: "Shoe Size") and label (eg: "Size"). The 'label' is used on the front-end of the website.
  * Click 'Save'
  
 2. Assign attribute types to the product
 
  * Check the newly created attribute type in the 'Variation Attribute Types' table.
  * Save and publish the product page.
  * Note that you should save the product after checking each attribute type. The attribute types may not be set otherwise.
  
 3. Create Attribute Values
 
  * Click the product attribute type edit icon.
  * Switch to the 'Values' tab.
  * Fill out 'Value' and 'Sort' table as required.
 
 Note, you can also manage product attribute types and values in the "Products" section:
 
  * Switch to the 'Products' section of the SilverStripe CMS.
  * Choose 'Product Attribute Type' from the 'Search for:' drop-down list on the left.
  * Find and click on the attribute type you want to add values to.
  * In the 'Values' tab, click the 'Add Product Attribute Value' link and add any number of values. You can set a sort value for each, if appropriate eg: "small" would need to come before "medium".

 
Creating a Product Variation
----------------------------

Once you have set up some attribute types and values, and then assigned some attribute types to your product,
you can create variations for it.

 * Find the 'Variations' tab for the product.
 * Click 'Add Product Variation'
 * Fill out a product code, and price.
 * Select attribute values from the attribute type drop-downs.
 * Save
 
 
Creating and updating variations from a spreadsheet
---------------------------------------------------

See [Bulk Loading Products](BulkLoadingProducts#ProductVariations.md).