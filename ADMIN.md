# Admin panel documentation

This package is used for integration with Mailerlite. It listens for selected events from the system and adds users to the appropriate groups in the service.

You can configure this package in the Admin Panel. To do this, select the Settings and then navigate to the mailerlite tab.
You need to set the `api_key` generated in the Mailerlite panel. And enable the package to start listening events from the LMS.

- `newsletter_field_key` - This field is used to check if the user accepted the marketing consent during registration if yes then the user is added to mailerlite.
- `group_registered_group` - This is the name of the group to which the user is added after registration in the system (if the user has accepted the consent).
- `group_order_paid` - This is the name of the group to which users who have paid for purchases on the platform are added.
- `group_left_cart` - This is the name of the group with users who added a product to the cart but didn't complete the purchase within 24 hours.
