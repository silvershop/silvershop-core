<% require css("silvershop/core: client/dist/css/account.css") %>

<% include SilverShop\Includes\AccountNavigation %>
<div id="Account" class="silvershop-typography">
    <h2 class="silvershop-pagetitle">
        <%t SilverShop\Page\AccountPage_AddressBook.Title 'Default Addresses' %>
    </h2>
    <%-- If you want the old dropdown system back you can just use $DefaultAddressForm here instead --%>
    <% if $CurrentMember.AddressBook %>
        <% loop $CurrentMember.AddressBook %>
            <div class="silvershop-panel silvershop-radius silvershop-address-panel $EvenOdd">
                <% if $ID == $CurrentMember.DefaultShippingAddressID %>
                    <span class="silvershop-tag silvershop-def-shipping">
                        <%t SilverShop\Page\AccountPage_AddressBook.DefaultShippingAddress 'Default Shipping Address' %>
                    </span>
                <% end_if %>
                <% if $ID == $CurrentMember.DefaultBillingAddressID %>
                    <span class="silvershop-tag silvershop-def-billing">
                        <%t SilverShop\Page\AccountPage_AddressBook.DefaultBillingAddress 'Default Billing Address' %>
                    </span>
                <% end_if %>
                <div class="silvershop-panel-body">
                    <% include SilverShop\Model\Address %>
                </div>
                <div class="silvershop-panel-footer silvershop-cf">
                    <% if $ID != $CurrentMember.DefaultShippingAddressID %>
                        <a title="<%t SilverShop\Page\AccountPage_AddressBook.MakeDefaultShippingTitle 'Make this my default shipping address' %>"
                           href="$Top.SetDefaultShippingLink($ID)" class="silvershop-btn">
                            <%t SilverShop\Page\AccountPage_AddressBook.MakeDefaultShipping 'Make Default Shipping' %>
                        </a>
                    <% end_if %>
                    <% if $ID != $CurrentMember.DefaultBillingAddressID %>
                        <a title="<%t SilverShop\Page\AccountPage_AddressBook.MakeDefaultBillingTitle 'Make this my default billing address' %>"
                           href="$Top.SetDefaultBillingLink($ID)" class="silvershop-btn">
                            <%t SilverShop\Page\AccountPage_AddressBook.MakeDefaultBilling 'Make Default Billing' %>
                        </a>
                    <% end_if %>
                    <a href="$Top.DeleteAddressLink($ID)"
                       class="silvershop-remove-address"
                       title="<%t SilverShop\Page\AccountPage_AddressBook.DeleteAddress 'Delete this address' %>">
                        <img src="$resourceURL('silvershop/core:client/dist/images/remove.gif')" alt="X">
                    </a>
                </div>
            </div>
        <% end_loop %>
    <% else %>
        <p class="silvershop-alert">
            <%t SilverShop\Page\AccountPage_AddressBook.NoAddress 'No addresses found.' %>
        </p>
    <% end_if %>
    <h2>
        <%t SilverShop\Page\AccountPage_AddressBook.CreateNewTitle 'Create New Address' %>
    </h2>
    $CreateAddressForm
</div>
