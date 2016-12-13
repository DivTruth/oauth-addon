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

# Setup provider and app if applicable
$oauth = explode( '/', get_query_var( 'oauth', FALSE ) );
$provider = $oauth[0];
$app = (ISSET($oauth[1])) ? $oauth[1] : false;

# Make sure an authentication code was received
if(!$provider) {
    die("<strong>ERROR:</strong> Unable to determine the provider");
}

# Create application OR provider oauth instance
if($app){
	# Dynamically load application class
	$application = new $app();
	$oauth = $application->oauth;
} else {
	# Dynamically load provider class
	$class = "OAuth_".ucfirst($provider);
	$oauth = new $class();
}

# Check for authentication
if(!$oauth->isAuthenticated()){
	# Complete Step 1: Consume the returned authorization code 
  	$oauth->consume_authorization_code( $_GET['code'] ); 

	# Step 2: Request OAuth tokens
	$token_response = $oauth->request_tokens();

	# Step 3: Consume tokens and authenticate session
	$oauth->consume_token_response($token_response);
}

# Allow applications to hook into complete
do_action( 'oauth_complete', '' );

# If a state was provided, parse it for features
$state = (ISSET($_REQUEST['state'])) ? $_REQUEST['state'] : false;
if($state){
	$features = explode( ',', $state );
	foreach ($features as $feature) {
		echo $feature;
		# Either activate the app or the provider feature method
		if($app){
			$application->$feature();
		} else {
			$oauth->$feature();
		}
	}
}

# Run the app or provider Activate method
if($app){
	$application->activate();
}

?>