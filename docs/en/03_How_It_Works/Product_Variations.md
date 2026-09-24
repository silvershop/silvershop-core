# Product Variations

Product variations provide a way to purchase predefined customisations of a product.

Often variations of a product will have their own unique SKU (product code).

## Data Model

The variation system has intentionally been kept seperate from the core code.
This is because not every website will need variations support, and thus it should be
simple to disable / remove. It's managed by `SilverShop\Extension\ProductVariationsExtension` which adds the following structure:

 * `SilverShop\Page\Product`
 	* has_many Variations => `SilverShop\Model\Variation\Variation`
 	   * many_many AttributeValues => `SilverShop\Model\Variation\AttributeValue`
 	* many_many VariationAttributeTypes => `SilverShop\Model\Variation\AttributeType`
 	   * has_many Values => `SilverShop\Model\Variation\AttributeValue`
 	
## Managing variations in the CMS

Variations are managed on a product's **Variations** tab.

1. **Choose the attributes.** In the *Attributes* field, pick the attribute types that describe how this
   product varies (e.g. Size, Colour) and **Save**. These are the axes of the variation matrix.
2. **Generate the matrix.** Click **Generate variations** to create the sellable combinations — the
   cartesian product of the selected attribute types' values. For example *Size* (S, M, L) × *Colour*
   (Red, Blue) produces six variations.

The **Generate variations** button is **idempotent and non-destructive**:

 * It only creates the combinations that are **missing** — existing variations are left untouched. So
   after adding a new value (say a new colour) you can click it again and it just fills in the gaps.
 * It never deletes variations — in particular it will not remove variations that are referenced by
   placed orders.
 * Newly generated variations are priced at the product's `BasePrice` by default.

### Editing variations inline

Each row in the Variations grid is one sellable combination and can be edited inline:

 * **Attribute dropdowns** — one per attribute type; change what a variation is.
 * **Code** (`InternalItemID`) and **Price** — edited directly in the row.
 * **Stock** — shown when the [`silvershop/stock`](https://github.com/silvershop/silverstripe-stock) module
   is installed.
 * **Drag to reorder**, and open a row's edit view to set a per-variation image.

Changes are saved with the product.

## Front-end Choosing a Variation

You can either provide a list of possible variations to the visitor, or present
a form for selecting the options they want. Each approach has pros and cons.

Listing all variations in a table is useful for presenting all possible variations,particularly when there are some obsucre combinations. For example, only having two options of: large red ball, or small green ball. Using a table becomes unpractical when the total number of variations for a particular product is high.

Presenting options in a form is probably a more common approach. It keeps the presentation
of options compact, and easy to comprehend.
You can provide additional javascript to instantly notify the visitor that a particular
combintation of options isn't available.
