<div class="accountnav">

    <div class="nav">

        <h2><%t AccountPage.Title 'My Account' %></h2>

        <ul class="nav nav-list">

            <li>
                <a href="{$Link}">
                    <i class="icon icon-list fa fa-list"></i><%t AccountPage.PastOrders 'Past Orders' %>
                </a>
            </li>

            <li>
                <a href="{$Link('editprofile')}">
                    <i class="icon icon-user fa fa-user"></i><%t AccountPage.EditProfile 'Edit Profile' %>
                </a>
            </li>

            <li>
                <a href="{$Link('addressbook')}">
                    <i class="icon icon-book fa fa-book"></i><%t AccountPage.AddressBook 'Address Book' %>
                </a>
            </li>

            <li>
                <a href="Security/logout">
                    <i class="icon icon-off fa fa-sign-out"></i><%t AccountPage.LogOut 'Log Out' %>
                </a>
            </li>

        </ul>

    </div>

    <div class="memberdetails">

        <% with $CurrentMember %>

            <dl>

                <dt><%t AccountPage.MemberName 'Name' %></dt>
                <dd>$Name</dd>

                <dt><%t AccountPage.MemberEmail 'Email' %></dt>
                <dd>$Email</dd>

                <dt><%t AccountPage.MemberSince 'Member Since' %></dt>
                <dd>$Created.Nice</dd>

                <dt><%t AccountPage.MemberLastVisit 'Last Visit' %></dt>
                <dd>$LastVisited.Nice</dd>

                <dt> <%t AccountPage.NumberOfOrders 'Number of orders' %></dt>
                <dd><% if $PastOrders %>{$PastOrders.Count}<% else %>0<% end_if %></dd>

            </dl>

        <% end_with %>

    </div>

</div>
