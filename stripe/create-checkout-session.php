<?php
// This server-side script creates a new Stripe Checkout session 

require 'vendor/autoload.php';

if ($_ENV['APP_ENV'] === 'local') { // Windows/XAMPP: ensure 'variables_order="EGPCS"' in php.ini and 'SetEnv APP_ENV local' in httpd-xampp.conf
    // FOR LOCAL TESTING ONLY get stripe secret key, domain and price ID via .env file
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load(); // This method will not throw an exception if .env file is not found
    \Stripe\Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);
    $YOUR_DOMAIN = $_ENV['DOMAIN'];
    $PRICE_ID = $_ENV['PRICE_ID'];
} else {
    // FOR PROD ONLY Attempt to load WordPress environment and stripe secret key, domain and price ID from wp-config.php
    if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php')) {
        require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');
    } else {
        error_log('Failed to load wp-load.php');
        http_response_code(500);
        echo json_encode(['error' => 'Failed to load wp-load.php']);
        exit;
    }

    if (defined('STRIPE_SECRET_KEY') && defined('DOMAIN') && defined('PRICE_ID')) {
        \Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);
        $YOUR_DOMAIN = DOMAIN;
        $PRICE_ID = PRICE_ID;
    } else {
        error_log('Required constants are not defined in wp-config.php');
        http_response_code(500);
        echo json_encode(['error' => 'Required constants are not defined in wp-config.php']);
        exit;
    }
}

header('Content-Type: application/json');
// Get the raw POST body
$body = file_get_contents("php://input");
// Decode the JSON object
$data = json_decode($body, true);
// Get email and cancelUrl from decoded JSON object
$email = filter_var($data['email'], FILTER_SANITIZE_EMAIL) ?? null;
$cancelUrl = filter_var($data['cancelUrl'], FILTER_SANITIZE_URL) ?? null;
// validate email and URLs
if ($email !== null && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid email']);
    exit;
}
if ($cancelUrl !== null && !filter_var($cancelUrl, FILTER_VALIDATE_URL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid URL']);
    exit;
}

try {
    $checkout_session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'line_items' => [[
            'price' => $PRICE_ID,
            'quantity' => 1,
        ]],
        'mode' => 'subscription',
        'customer_email' => $email,
        'success_url' => $YOUR_DOMAIN . '/success',
        'cancel_url' => $cancelUrl,
    ]);

    echo json_encode(['id' => $checkout_session->id]);
} catch (\Stripe\Exception\ApiErrorException $e) {
    $error_message = 'Stripe API Error: ' . $e->getMessage();
    error_log($error_message);
    http_response_code(500);
    echo json_encode(['Stripe API Error' => $error_message]);
} catch (Exception $e) {
    $error_message = 'Uncaught Error: ' . $e->getMessage();
    error_log($error_message);
    http_response_code(500);
    echo json_encode(['Error' => 'An error occurred. Please try again later.']);
}
