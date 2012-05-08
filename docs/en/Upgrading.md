# Upgrading Information and Troubleshooting

This page is intended to make you aware of upgrade issues you may face,
and how to resolve them.

Don't forget to run the following url commands when you upgrade the
shop module:

    [yourdomain.com]/dev/build?flush=all
    [yourdomain.com]/tasks/ShopMigrationTask

# 0.8.4

## CSS is lost / templates 

CSS files are now expected to be included by templates, or by your own
page / decorator requirements. This change was made to allow flexibility
of what css files to include. All the default templates now have requires
statements like this:

    <% require themedCSS(product) %>
    
To fix in your site, update your templates to include the appropriate css
files as per above. If you want a more advanced solution, you could
add requirements calls to your Page_Controller init function, or
use an extension.

## Modifiers breaking

If you have written custom modifiers, or are using old modifiers, they may stop working
properly.

 * Values not showing up
 * Values not being added to total
 
If the modifier has an Amount field, this should be deprecated and replaced with
a value field, which takes an $incoming argument.


# older / other

## Payment values / amounts have gone missing

The payment module switched the "Amount" field from using the Currency DBField to Money. Money
is a CompositeDBField that combines AmountAmmount and AmountCurrency. The "Currency" field
was also dropped. [Related diff](https://github.com/silverstripe-labs/silverstripe-payment/commit/8f27918294ac34b688f137e36b424616df55dd7f#diff-4)

To fix: rename your database Amount column to AmountAmount, and your Currency column to AmountCurrency.