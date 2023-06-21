<?php
// get-publishable-key.php

// Load WordPress environment
require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');

// Get the publishable key from wp-config.php
$publishableKey = defined('STRIPE_PUBLISHABLE_KEY') ? STRIPE_PUBLISHABLE_KEY : '';

// Return the publishable key as a JSON response
header('Content-Type: application/json');
echo json_encode(['publishableKey' => $publishableKey]);
