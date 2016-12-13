<?php
/**
 * OAuth Callback Script
 *
 * @package    OAuth
 * @author     Nick Worth
 */

do_action( 'oauth_callback', '' );

# Make sure an authentication code was received
if(!isset($_GET['code'])) {
    die("<strong>ERROR:</strong> No authorizaton code provided");
}

$provider = get_query_var( 'oauth', FALSE ); 

# Make sure an authentication code was received
if(!$provider) {
    die("<strong>ERROR:</strong> Unable to determine the provider");
}

# Dynamically load provider class
$class = "OAuth_".ucfirst($provider);
$oauth = new $class();

# Check for authentication
if(!$oauth->isAuthenticated()){
	# Complete Step 1: Consume the returned authorization code
	$oauth->consume_authorization_code( $_GET['code'] );

	# Step 2: Request OAuth tokens
	$token_response = $oauth->request_tokens();

	# Step 3: Consume tokens and authenticate session
	$oauth->consume_token_response($token_response);
}

$oauth->activate();

?>