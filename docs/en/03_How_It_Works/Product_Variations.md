# Product Variations

Product variations provide a way to purchase predefined customisations of a product.

Often variations of a product will have their own unique SKU (product code).

## Data Model

The variation system has intentionally been kept seperate from the core code.
This is because not every website will need variations support, and thus it should be
simple to disable / remove.

 * Product
 	* has_many ProductVariation
 	   * many_many AttributeValues
 	* many_many VariationAttributeTypes
 	   * has_many AttributeValues
 	
## Front-end Choosing a Variation

You can either provide a list of possible variations to the visitor, or present
a form for selecting the options they want. Each approach has pros and cons.

Listing all variations in a table is useful for presenting all possible variations,
particularly when there are some obsucre combinations. For example, only having two
options of: large red ball, or small green ball. Using a table becomes un practical
when the total number of variations for a particular product is high.

Presenting options in a form is probably a more common approach. It keeps the presentation
of options compact, and easy to comprehend.
You can provide additional javascript to instantly notify the visitor that a particular
combintation of options isn't available.