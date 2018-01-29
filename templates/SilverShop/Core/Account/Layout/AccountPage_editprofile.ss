<% require css("silvershop/core: css/account.css") %>
<% require themedCSS("shop") %>
<% require themedCSS("account") %>

<% include SilverShop\Core\Account\AccountNavigation %>
<div id="Account" class="typography">

    <h2 class="pagetitle">
        <%t AccountPage_EditProfile.Title 'Edit Profile' %>
    </h2>

    $EditAccountForm
    $ChangePasswordForm

</div>
