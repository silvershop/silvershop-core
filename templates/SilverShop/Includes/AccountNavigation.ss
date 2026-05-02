<div class="silvershop-accountnav">
    <div class="silvershop-nav">
        <h2><%t SilverShop\Page\AccountPage.Title 'My Account' %></h2>
        <ul class="silvershop-nav silvershop-nav-list">
            <li>
                <a href="{$Link}">
                    <i class="silvershop-icon silvershop-icon-list fa fa-list"></i><%t SilverShop\Page\AccountPage.PastOrders 'Past Orders' %>
                </a>
            </li>
            <li>
                <a href="{$Link('editprofile')}">
                    <i class="silvershop-icon silvershop-icon-user fa fa-user"></i><%t SilverShop\Page\AccountPage.EditProfile 'Edit Profile' %>
                </a>
            </li>
            <li>
                <a href="{$Link('addressbook')}">
                    <i class="silvershop-icon silvershop-icon-book fa fa-book"></i><%t SilverShop\Page\AccountPage.AddressBook 'Address Book' %>
                </a>
            </li>
            <li>
                <a href="Security/logout">
                    <i class="silvershop-icon silvershop-icon-off fa fa-sign-out"></i><%t SilverShop\Page\AccountPage.LogOut 'Log Out' %>
                </a>
            </li>
        </ul>
    </div>
    <div class="silvershop-memberdetails">
        <% with $CurrentMember %>
            <dl>
                <dt><%t SilverShop\Page\AccountPage.MemberName 'Name' %></dt>
                <dd>$Name</dd>

                <dt><%t SilverShop\Page\AccountPage.MemberEmail 'Email' %></dt>
                <dd>$Email</dd>

                <dt><%t SilverShop\Page\AccountPage.MemberSince 'Member Since' %></dt>
                <dd>$Created.Nice</dd>

                <dt> <%t SilverShop\Page\AccountPage.NumberOfOrders 'Number of orders' %></dt>
                <dd><% if $PastOrders %>{$PastOrders.Count}<% else %>0<% end_if %></dd>
            </dl>
        <% end_with %>
    </div>
</div>
