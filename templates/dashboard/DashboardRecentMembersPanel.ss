<div class="shop-dashboard dashboard-recent-orders">
    <table class="table table-bordered">
        <thead>
            <tr>
                <td><%t AccountPage.MemberSince "Member Since" %></td>
                <th><%t AccountPage.MemberEmail "Email" %></th>
                <th><%t AccountPage.MemberName "Name" %></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <% loop $Members %>
                <tr>
                    <td>$Created.Nice</td>
                    <td>$Email</td>
                    <td>$Surname, $FirstName</td>
                    <td>
                        <a class="ss-ui-button ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" role="button" aria-disabled="false" href="admin/security/EditForm/field/Members/item/$ID/edit">
                            <%t Shop.Edit "Edit" %>
                        </a>
                    </td>
                </tr>
            <% end_loop %>
        </tbody>
    </table>
</div>
