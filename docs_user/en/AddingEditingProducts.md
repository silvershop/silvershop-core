# Adding and updating a single product

There are two interfaces you can use for editing products: using the 'Pages' section, or using the 'Products' section of the CMS.
Products are considered a special type of page. [This documentation](http://userhelp.silverstripe.org/for-website-content-editors/creating-and-editing-content/) explains how to create and edit a standard page on in the CMS.

To create a product, you first need to select the parent page (typically one of the product groups)
![Add Product](\images\createproduct.jpg)

..or if you want to edit an existing product, simply select the product in the page tree on the left.
once you've selected or created a product in the page tree, it should then show as being selected with a purple/blue background.

## Explanation of Fields

Here is a quick explanation of each field in the product editing form.

 * Page/Product Name - used for customers to identify a product. This will show up on product pages, group pages, checkout, invoices etc.
 * Navigation Label - used in website menus.
 * Product Code - unique product identifier for internal use, and it also helps uniquely and quickly reference a product. Also known as SKU or Internal ID.
 * Price - depending on your website configuration, this may be required for the product to be buyable, or even visible.
 * Weight - often used with shipping calculations. You may not need to fill out this field.
 * Model
 * Featured product - if selected this product will show first when viewing a product group. 
 * Allow product to be purchased
 * Content - description of the product.


## Choosing an image

The default eCommerce system allows you to add an image for each product.

 * Find the 'Content > Image' tab/sub-tab.
 * Select an image either from your computer, or from the file store.
 * Click 'attach image.
 * Save and Publish your product page to complete adding the image.

 
## Set additional product groups

In the 'Content > Product Groups' tab you can specify secondary product groups that the product should appear in.

## Creating and editing products with the 'Products' section of the CMS 

Another way to manage products is through the 'Products' section of the CMS. Here you can list products according to a number of search options, such as id,name, weight, price. 

This is also where you can upload a spreadsheet of products. See [bulk loading products](BulkLoadingProducts)