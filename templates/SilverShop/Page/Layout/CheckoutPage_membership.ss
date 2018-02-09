<% require css("silvershop/core: client/dist/css/checkout.css") %>

<h1 class="pageTitle">$Title</h1>
<div id="Checkout">
    <div class="typography">
        <% if $Content %>
            $Content
        <% end_if %>
    </div>
    <div class="membership">
        $Form
    </div>
    <div class="login">
        <h2><%t SilverStripe\Security\Security.LOGIN 'Log In' %></h2>
        $LoginForm
    </div>
</div>
