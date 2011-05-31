## Member functionality

If a member is logged in when they place an order, that order will be associated with their account.
The rest of the time they may log in / out and the order will remain associated with the session.

Config settings:

	OrderForm::set_user_membership_optional(true); //does the user have the choice in becomign a member?
	OrderForm::set_force_membership(false);

![Membership Flow Chart](\images\membership-flow-chart.jpg)
