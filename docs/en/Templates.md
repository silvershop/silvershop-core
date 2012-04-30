# Templates

The shop module tempaltes have been broken down into small, reusable parts. This allows a greater freedom of customisation.

Here is the heirarchy of templates:

Order.ss
	Order_Shipping.ss
	Order_Content.ss
	Order_Payments.ss

ProductCategory.ss
	ProductGroupItem.ss
	

# Getting the cart in templates

For convience, $Cart is available on any template for a Page_Controller, or sub-page type.

This is provided by the ViewableCart class, which is an Extension for Page_Controller.
The Cart template function also handles recalculating the cart, just in time for use within templates.