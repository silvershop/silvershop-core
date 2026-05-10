<div class="silvershop-account-nav">
    <div class="silvershop-account-nav__menu">
        <h2 class="silvershop-account-nav__title"><%t SilverShop\Page\AccountPage.Title 'My Account' %></h2>
        <ul class="silvershop-account-nav__list">
            <li class="silvershop-account-nav__item">
                <a class="silvershop-account-nav__link" href="{$Link}">
                    <i class="silvershop-account-nav__icon silvershop-account-nav__icon--list fa fa-list"></i><%t SilverShop\Page\AccountPage.PastOrders 'Past Orders' %>
                </a>
            </li>
            <li class="silvershop-account-nav__item">
                <a class="silvershop-account-nav__link" href="{$Link('editprofile')}">
                    <i class="silvershop-account-nav__icon silvershop-account-nav__icon--user fa fa-user"></i><%t SilverShop\Page\AccountPage.EditProfile 'Edit Profile' %>
                </a>
            </li>
            <li class="silvershop-account-nav__item">
                <a class="silvershop-account-nav__link" href="{$Link('addressbook')}">
                    <i class="silvershop-account-nav__icon silvershop-account-nav__icon--book fa fa-book"></i><%t SilverShop\Page\AccountPage.AddressBook 'Address Book' %>
                </a>
            </li>
            <li class="silvershop-account-nav__item">
                <a class="silvershop-account-nav__link" href="Security/logout">
                    <i class="silvershop-account-nav__icon silvershop-account-nav__icon--off fa fa-sign-out"></i><%t SilverShop\Page\AccountPage.LogOut 'Log Out' %>
                </a>
            </li>
        </ul>
    </div>
    <div class="silvershop-account-nav__member">
        <% with $CurrentMember %>
            <dl class="silvershop-account-nav__member-details">
                <dt class="silvershop-account-nav__member-label"><%t SilverShop\Page\AccountPage.MemberName 'Name' %></dt>
                <dd class="silvershop-account-nav__member-value">$Name</dd>

                <dt class="silvershop-account-nav__member-label"><%t SilverShop\Page\AccountPage.MemberEmail 'Email' %></dt>
                <dd class="silvershop-account-nav__member-value">$Email</dd>

                <dt class="silvershop-account-nav__member-label"><%t SilverShop\Page\AccountPage.MemberSince 'Member Since' %></dt>
                <dd class="silvershop-account-nav__member-value">$Created.Nice</dd>

                <dt class="silvershop-account-nav__member-label"> <%t SilverShop\Page\AccountPage.NumberOfOrders 'Number of orders' %></dt>
                <dd class="silvershop-account-nav__member-value"><% if $PastOrders %>{$PastOrders.Count}<% else %>0<% end_if %></dd>
            </dl>
        <% end_with %>
    </div>
</div>
