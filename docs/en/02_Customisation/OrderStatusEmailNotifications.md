## Order Status Email Notifications
To send emails to customers after changes, start by listing the statuses where an order will be logged:
```yaml
#in mysite/config.yml
Order:
  log_status:
    - Processing
    - Sent
    - MemberCancelled
    - AdminCancelled  
```
Next, customise the email by overriding Silvershop's existing settings:
```yaml
#in mysite/lang/en.yml
en:
  ShopEmail:
    PhoneNumber: 123 4567
    Regards: Regards
    StatusChangeSubject: 'Business Name - {Title}'
    StatusChangeTitle: 'Order Status Change'
    StatusChangeAdminCancelledNote: 'Your note for the email body when order status is AdminCancelled'
    StatusChangeMemberCancelledNote: 'Your note for the email body when order status is MemberCancelled'
    StatusChangeProcessingNote: 'Your note for the email body when order status is Processing'
    StatusChangeSentNote: 'Your note for the email body when order status is Sent' 
```
To further customise the email, copy the template `Order_StatusEmail.ss` from `silvershop/templates/email` folder and paste to `{yourtheme}/templates/email` and make the required adjustments.

