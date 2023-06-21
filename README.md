# stripe-wordpress-public
Hosted Stripe Checkout via Wordpress email field

OVERVIEW
Initiates Stripe Hosted Checkout via an email field with a "Checkout" button for one subscription product.

INSTRUCTIONS
Local machine:
- Change env.example to .env and populate the placeholder values with your own
- Point server at root directory and visit /local-checkout.html in your browser

Production:
- Copy stripe folder into same folder as wp-config.php (.env file is not required for production)
- Copy env.example contents into wp-config.php and replace placeholder values with your own
- Copy prod-client-checkout-button.html code into a custom HTML block where you want the email field & checkout button to be
