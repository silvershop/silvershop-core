<% require css("silvershop/core: client/dist/css/account.css") %>

<% include SilverShop\Includes\AccountNavigation %>
<div id="Account" class="typography">
    <h2 class="pagetitle">
        <%t SilverShop\Page\AccountPage_AddressBook.Title 'Default Addresses' %>
    </h2>
    <%-- If you want the old dropdown system back you can just use $DefaultAddressForm here instead --%>
    <% if $CurrentMember.AddressBook %>
        <% loop $CurrentMember.AddressBook %>
            <div class="panel radius address-panel $EvenOdd">
                <% if $ID == $CurrentMember.DefaultShippingAddressID %>
                    <span class="tag def-shipping">
                        <%t SilverShop\Page\AccountPage_AddressBook.DefaultShippingAddress 'Default Shipping Address' %>
                    </span>
                <% end_if %>
                <% if $ID == $CurrentMember.DefaultBillingAddressID %>
                    <span class="tag def-billing">
                        <%t SilverShop\Page\AccountPage_AddressBook.DefaultBillingAddress 'Default Billing Address' %>
                    </span>
                <% end_if %>
                <div class="panel-body">
                    <% include SilverShop\Model\Address %>
                </div>
                <div class="panel-footer cf">
                    <% if $ID != $CurrentMember.DefaultShippingAddressID %>
                        <a title="<%t SilverShop\Page\AccountPage_AddressBook.MakeDefaultShippingTitle 'Make this my default shipping address' %>"
                           href="$Top.Link('setdefaultshipping')/{$ID}" class="btn">
                            <%t SilverShop\Page\AccountPage_AddressBook.MakeDefaultShipping 'Make Default Shipping' %>
                        </a>
                    <% end_if %>
                    <% if $ID != $CurrentMember.DefaultBillingAddressID %>
                        <a title="<%t SilverShop\Page\AccountPage_AddressBook.MakeDefaultBillingTitle 'Make this my default billing address' %>"
                           href="$Top.Link('setdefaultbilling')/{$ID}" class="btn">
                            <%t SilverShop\Page\AccountPage_AddressBook.MakeDefaultBilling 'Make Default Billing' %>
                        </a>
                    <% end_if %>
                    <a href="$Top.Link('deleteaddress')/{$ID}"
                       class="remove-address"
                       title="<%t SilverShop\Page\AccountPage_AddressBook.DeleteAddress 'Delete this address' %>">
                        <img src="$resourceURL('silvershop/core:client/dist/images/remove.gif')" alt="X">
                    </a>
                </div>
            </div>
        <% end_loop %>
    <% else %>
        <p class="alert">
            <%t SilverShop\Page\AccountPage_AddressBook.NoAddress 'No addresses found.' %>
        </p>
    <% end_if %>
    <h2>
        <%t SilverShop\Page\AccountPage_AddressBook.CreateNewTitle 'Create New Address' %>
    </h2>
    $CreateAddressForm
</div>
