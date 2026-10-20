# TechTopsy-MiniBlog

## Premium classified access

Copy `.env.example` to `.env` and set `PAYSTACK_SECRET_KEY` to a Paystack test or live secret key. Keep `.env` private; it is excluded from Git. The subscription price is configured in `controllers/SubscriptionController.php` and currently set to NGN 3,000 per 30 days.

Import `schema.sql` to create the premium post flag and subscriptions table. The script preserves an existing admin password hash when run again.

Administrators and editors can mark a post as classified in the admin post form. Readers can browse `index.php?page=classified`; full article content is restricted to users with an active subscription. Checkout redirects to Paystack and access is granted only after the server verifies the transaction reference, amount, and currency.
