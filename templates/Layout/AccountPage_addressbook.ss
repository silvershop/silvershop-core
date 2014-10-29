<% require themedCSS(account,shop) %>
<% include AccountNavigation %>

<div class="typography">

    <h2 class="pagetitle">
        <%t AccountPage_AddressBook.Title 'Default Addresses' %>
    </h2>

    <% if $DefaultAddressForm %>

        $DefaultAddressForm

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