<div class="accountnav">

    <div class="nav">

        <h2><%t AccountNavigation.Title 'My Account' %></h2>

        <ul class="nav nav-list">

            <li>
                <a href="{$Link}">
                    <i class="icon icon-list"></i><%t AccountNavigation.PastOrders 'Past Orders' %>
                </a>
            </li>

            <li>
                <a href="{$Link(editprofile)}">
                    <i class="icon icon-user"></i><%t AccountNavigation.EditProfile 'Edit Profile' %>
                </a>
            </li>

            <li>
                <a href="{$Link(addressbook)}">
                    <i class="icon icon-book"></i><%t AccountNavigation.AddressBook 'Address Book' %>
                </a>
            </li>

            <li>
                <a href="Security/logout">
                    <i class="icon icon-off"></i><%t AccountNavigation.LogOut 'Log Out' %>
                </a>
            </li>

        </ul>

    </div>

    <div class="memberdetails">

        <% with $CurrentMember %>

            <dl>

                <dt><%t AccountNavigation.MemberName 'Name' %></dt>
                <dd>$Name</dd>

                <dt><%t AccountNavigation.MemberEmail 'Email' %></dt>
                <dd>$Email</dd>

                <dt><%t AccountNavigation.MemberSince 'Member Since' %></dt>
                <dd>$Created.Nice</dd>

                <dt><%t AccountNavigation.MemberLastVisit 'Last Visit' %></dt>
                <dd>$LastVisited.Nice</dd>

                <dt> <%t AccountNavigation.NumberOfOrders 'Number of orders' %></dt>
                <dd><% if $PastOrders %>{$PastOrders.Count}<% else %>0<% end_if %></dd>

            </dl>

        <% end_with %>

    </div>

</div>