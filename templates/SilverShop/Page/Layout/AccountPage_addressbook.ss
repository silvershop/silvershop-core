<% if $SilverShopIncludeDefaultStyles %>
<% require css("silvershop/core:client/dist/css/silvershop-base.css") %>
<% require css("silvershop/core:client/dist/css/account.css") %>
<% end_if %>

<% include SilverShop\Includes\AccountNavigation %>
<div class="silvershop-account silvershop-account--address-book silvershop-typography">
    <h2 class="silvershop-account__title">
        <%t SilverShop\Page\AccountPage_AddressBook.Title 'Default Addresses' %>
    </h2>
    <%-- If you want the old dropdown system back you can just use $DefaultAddressForm here instead --%>
    <% if $CurrentMember.AddressBook %>
        <% loop $CurrentMember.AddressBook %>
            <div class="silvershop-address-panel $EvenOdd">
                <% if $ID == $CurrentMember.DefaultShippingAddressID %>
                    <span class="silvershop-address-panel__tag silvershop-address-panel__tag--shipping">
                        <%t SilverShop\Page\AccountPage_AddressBook.DefaultShippingAddress 'Default Shipping Address' %>
                    </span>
                <% end_if %>
                <% if $ID == $CurrentMember.DefaultBillingAddressID %>
                    <span class="silvershop-address-panel__tag silvershop-address-panel__tag--billing">
                        <%t SilverShop\Page\AccountPage_AddressBook.DefaultBillingAddress 'Default Billing Address' %>
                    </span>
                <% end_if %>
                <div class="silvershop-address-panel__body">
                    <% include SilverShop\Model\Address %>
                </div>
                <div class="silvershop-address-panel__footer">
                    <% if $ID != $CurrentMember.DefaultShippingAddressID %>
                        <a title="<%t SilverShop\Page\AccountPage_AddressBook.MakeDefaultShippingTitle 'Make this my default shipping address' %>"
                           href="$Top.SetDefaultShippingLink($ID)" class="silvershop-address-panel__btn">
                            <%t SilverShop\Page\AccountPage_AddressBook.MakeDefaultShipping 'Make Default Shipping' %>
                        </a>
                    <% end_if %>
                    <% if $ID != $CurrentMember.DefaultBillingAddressID %>
                        <a title="<%t SilverShop\Page\AccountPage_AddressBook.MakeDefaultBillingTitle 'Make this my default billing address' %>"
                           href="$Top.SetDefaultBillingLink($ID)" class="silvershop-address-panel__btn">
                            <%t SilverShop\Page\AccountPage_AddressBook.MakeDefaultBilling 'Make Default Billing' %>
                        </a>
                    <% end_if %>
                    <a href="$Top.DeleteAddressLink($ID)"
                       class="silvershop-address-panel__remove"
                       title="<%t SilverShop\Page\AccountPage_AddressBook.DeleteAddress 'Delete this address' %>">
                        <img class="silvershop-address-panel__remove-icon" src="$resourceURL('silvershop/core:client/dist/images/remove.gif')" alt="X">
                    </a>
                </div>
            </div>
        <% end_loop %>
    <% else %>
        <p class="silvershop-message silvershop-message--warning">
            <%t SilverShop\Page\AccountPage_AddressBook.NoAddress 'No addresses found.' %>
        </p>
    <% end_if %>
    <h2 class="silvershop-account__title">
        <%t SilverShop\Page\AccountPage_AddressBook.CreateNewTitle 'Create New Address' %>
    </h2>
    $CreateAddressForm
</div>
