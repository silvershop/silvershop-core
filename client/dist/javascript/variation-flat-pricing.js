/**
 * Live-toggles the per-variation "Price" column in the product Variations grid when the
 * "Price all variations from the base price" checkbox is changed, without a save + reload.
 *
 * The body cells (td.col-Price) are hidden by CSS via the grid's "flat-pricing" class. The
 * header <th> can't be hidden the same way — for a sortable column its class is derived from
 * the sort action, not the column name — so we hide it by matching the Price column's index
 * (header and body columns align 1:1). With JS off, the saved state still renders correctly
 * because the server hides the body cells via the class.
 */
(function ($) {
    function apply(grid, flat) {
        grid.toggleClass('flat-pricing', flat);
        var priceCell = grid.find('tbody tr').first().children('.col-Price');
        if (priceCell.length) {
            grid.find('tr.sortable-header th').eq(priceCell.index()).css('display', flat ? 'none' : '');
        }
    }

    $.entwine('silvershop.variations', function ($) {
        $('.cms-edit-form input[name="PriceVariationsFromBase"]').entwine({
            onmatch: function () {
                this._super();
                this.syncVariationPriceColumn();
            },
            onchange: function () {
                this.syncVariationPriceColumn();
            },
            syncVariationPriceColumn: function () {
                apply(this.closest('form').find('.variations-grid'), this.is(':checked'));
            }
        });

        // Re-apply whenever the grid (re)renders — e.g. after pagination or an inline save.
        $('.cms-edit-form .variations-grid').entwine({
            onmatch: function () {
                this._super();
                var checkbox = this.closest('form').find('input[name="PriceVariationsFromBase"]');
                apply(this, checkbox.length ? checkbox.is(':checked') : this.hasClass('flat-pricing'));
            }
        });
    });
})(jQuery);
