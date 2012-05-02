# Upgrading

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