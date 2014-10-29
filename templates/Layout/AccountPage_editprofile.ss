<% require themedCSS(account, shop) %>
<% include AccountNavigation %>

<div class="typography">

    <h2 class="pagetitle">
        <%t AccountPage_EditProfile.Title 'Edit Profile' %>
    </h2>

    $EditAccountForm
    $ChangePasswordForm

</div>