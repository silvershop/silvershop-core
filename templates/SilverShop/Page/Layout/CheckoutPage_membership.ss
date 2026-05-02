<% require css("silvershop/core: client/dist/css/checkout.css") %>

<h1 class="silvershop-pageTitle">$Title</h1>
<div id="Checkout">
    <div class="silvershop-typography">
        <% if $Content %>
            $Content
        <% end_if %>
    </div>
    <div class="silvershop-membership">
        $Form
    </div>
    <div class="silvershop-login">
        <h2><%t SilverStripe\Security\Security.LOGIN 'Log In' %></h2>
        $LoginForm
    </div>
</div>
