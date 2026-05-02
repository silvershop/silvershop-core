<% require css("silvershop/core: client/dist/css/account.css") %>

<% include SilverShop\Includes\AccountNavigation %>
<div id="Account" class="silvershop-typography">

    <h2 class="silvershop-pagetitle">
        <%t SilverShop\Page\AccountPage_EditProfile.Title 'Edit Profile' %>
    </h2>

    $EditAccountForm
    $ChangePasswordForm

</div>
