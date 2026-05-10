<% require css("silvershop/core: client/dist/css/account.css") %>

<% include SilverShop\Includes\AccountNavigation %>
<div class="silvershop-account silvershop-account--edit-profile silvershop-typography">

    <h2 class="silvershop-account__title">
        <%t SilverShop\Page\AccountPage_EditProfile.Title 'Edit Profile' %>
    </h2>

    <div class="silvershop-account__form silvershop-account__form--edit">
        $EditAccountForm
    </div>
    <div class="silvershop-account__form silvershop-account__form--password">
        $ChangePasswordForm
    </div>

</div>
