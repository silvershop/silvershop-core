<% require css("silvershop/css/account.css") %>
<% require themedCSS("shop") %>
<% require themedCSS("account") %>

<% include AccountNavigation %>

<div id="Account" class="typography">
    <h2 class="pagetitle">
        <%t AccountPage_AddressBook.Title 'Default Addresses' %>
    </h2>
    <%-- If you want the old dropdown system back you can just use $DefaultAddressForm here instead --%>
    <% if $CurrentMember.AddressBook %>
        <% loop $CurrentMember.AddressBook %>
            <div class="panel radius address-panel $EvenOdd">
                <% if $ID == $CurrentMember.DefaultShippingAddressID %>
                    <span class="tag def-shipping">
                        <%t AccountPage_AddressBook.DefaultShippingAddress 'Default Shipping Address' %>
                    </span>
                <% end_if %>
                <% if $ID == $CurrentMember.DefaultBillingAddressID %>
                    <span class="tag def-billing">
                        <%t AccountPage_AddressBook.DefaultBillingAddress 'Default Billing Address' %>
                    </span>
                <% end_if %>
                <div class="panel-body">
                    <% include Address %>
                </div>
                <div class="panel-footer cf">
                    <% if $ID != $CurrentMember.DefaultShippingAddressID %>
                        <a title="<%t AccountPage_AddressBook.MakeDefaultShippingTitle 'Make this my default shipping address' %>"
                           href="account/setdefaultshipping/{$ID}" class="btn">
                            <%t AccountPage_AddressBook.MakeDefaultShipping 'Make Default Shipping' %>
                        </a>
                    <% end_if %>
                    <% if $ID != $CurrentMember.DefaultBillingAddressID %>
                        <a title="<%t AccountPage_AddressBook.MakeDefaultBillingTitle 'Make this my default billing address' %>"
                           href="account/setdefaultbilling/{$ID}" class="btn">
                            <%t AccountPage_AddressBook.MakeDefaultBilling 'Make Default Billing' %>
                        </a>
                    <% end_if %>
                    <a href="account/deleteaddress/{$ID}"
                       class="remove-address"
                       title="<%t AccountPage_AddressBook.DeleteAddress 'Delete this address' %>">
                        <img src="silvershop/images/remove.gif" alt="X">
                    </a>
                </div>
            </div>
        <% end_loop %>
    <% else %>
        <p class="alert">
            <%t AccountPage_AddressBook.NoAddress 'No addresses found.' %>
        </p>
    <% end_if %>
    <h2>
        <%t AccountPage_AddressBook.CreateNewTitle 'Create New Address' %>
    </h2>
    $CreateAddressForm
</div>
