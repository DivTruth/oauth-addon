<?php 
/**
 * OAuth Application
 * 		This application is used to perform OAuth 2.0 handshakes, used
 * 		by provider classes as a framework obtaining authentication and
 * 		identity
 * 		
 * @package 	OAuth
 * @author 	   	Nick Worth
 */
if ( ! defined( 'ABSPATH' ) ) exit;

abstract class OAuthProvider {

	# Set class details
	protected $provider;
	public $session_string;

	# Provider authentication
	public $client_id;
	public $client_secret;
	public $redirect_uri;

	# OAuth URLs
	protected $auth_url;
	protected $tokens_url;
	protected $identity_url;

	/**
	 * Connector $_Session array
	 * @var array
	 */
	public $session;

	/**
	 * Store features in state parameter
	 * NOTE: Must be enabled by provider
	 * @var string
	 */
	public $state;
	protected $state_support = FALSE;

	/**
	 * Identity array
	 * @var array
	 */
	protected $identity;

	/**
	 * OAuth Constructor
	 */
	public function __construct( ) {}

	/**
	 * Once the provider is configured it needs to be installed
	 */
	protected function install(){
		# Check for state parameter support
		if($this->state_support){
			# Set state if called by specific feature
			$this->state = (ISSET($_REQUEST['state'])) ? $_REQUEST['state'] : false;
		} else {
			# Disable for this instance
			$this->state = FALSE;
		}
		# Setup the instance session container
		$this->session_string = ($this->state) ? $this->provider.'-'.$this->state : $this->provider;
		$this->setup_session();
	}

	/**
	 * Install the application and session string
	 *
	 * @param      string  $app
	 */
	public function install_app($app){
		$this->session_string = $app;
		$this->redirect_uri = $this->redirect_uri.'/'.$this->session_string;
		$this->setup_session();
	}

	/**
	 * Setup instance session
	 */
	private function setup_session(){
		if (!isset($_SESSION)) session_start();
		if(ISSET($_SESSION[$this->session_string])){
			foreach ($_SESSION[$this->session_string] as $key => $value) {
				$this->session[$key] = $value;
			}
		}
	}

	/**
	 * Clears the login state
	 */
	protected function clear_login_state(){
		unset($_SESSION[$this->session_string]);
	}

	/**
	 * Check for an authenticated session
	 *
	 * @param      string  $token
	 */
	public function isAuthenticated(){
		if( ISSET($_SESSION[$this->session_string]['access_token']) ){
			# Has an access token but could be expired
			if( ISSET($_SESSION[$this->session_string]['expires']) ){
				# Determine if token has expired
            	$expires = $_SESSION[$this->session_string]['expires'];
	            $current = time();
	            if( $expires >= $current ){
	                $this->token = $_SESSION[$this->session_string]['access_token'];
	                return TRUE;
	            } else {
	            	# Since token is expired, reset and try again
					$this->clear_login_state();
	            	return FALSE;
	            }
			} else {
				# No expiration set, so reset and try again
				$this->clear_login_state();
				return false;
			}
        }
		return FALSE;
	}

	/**
	 * Gets the authorization url
	 *
	 * @return     string
	 */
	private function get_authorization_url(){
		$params = array(
			'response_type' => 'code',
			'client_id' 	=> $this->client_id,
			'redirect_uri' 	=> $this->redirect_uri,
			'state' 		=> $this->state
		);
		# Allow provider to modify parameters
		$params = apply_filters( 'oauth_authorization_parameters', $params );
		# Return url with a filter hook for modifications
		return apply_filters( 'oauth_authorization_url', $this->auth_url .'?'. http_build_query($params) );
	}

	/**
	 * Gets the oauth code
	 */
	public function request_authorization_code() {
		$url = $this->get_authorization_url();
		$_SESSION[$this->session_string]['last_url'] = (ISSET($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : get_bloginfo('url' ).$_SERVER['REQUEST_URI'];
		header("Location: $url");
		exit;
	}

	/**
	 * Consume the authorization code
	 *
	 * @param      string  $authCode
	 */
	public function consume_authorization_code($auth_code){
		$this->set_field('authorization_code', $auth_code);
	}

	/**
	 * Sets the field value
	 *
	 * @param      string  $value
	 */
	protected function set_field($field, $value){
		$this->session[$field] = $value;
		$_SESSION[$this->session_string][$field] = $value;
	}

	/**
	 * Gets the token url
	 *
	 * @return     string
	 */
	private function get_token_url(){
		# Return url with a filter hook for modifications
		return apply_filters( 'oauth_token_url', $this->tokens_url );
	}

	/**
	 * Gets the oauth tokens
	 */
	public function request_tokens() {
		# Setup token request parameters:
		$params = array(
			'grant_type' 	=> 'authorization_code',
			'code' 			=> $this->session['authorization_code'],
			'client_id' 	=> $this->client_id,
			'client_secret' => DIV\services\helper::decrypt($this->client_secret),
			'redirect_uri' 	=> $this->redirect_uri,
			'state' 		=> $this->state
		);
		apply_filters( 'oauth_token_parameters', $params );

		# Attempt curl token request:
		$url = $this->get_token_url();
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
		$result = curl_exec($curl);
		# Check for curl error
		if(curl_errno($curl))
    		$this->end_login('Curl error: '.curl_error($curl));
		curl_close($curl);

		# Parse & handle the result:
		$response = json_decode($result, true);
		return $response;
	}

	/**
	 * Consume token response
	 *
	 * @param      array  $response
	 */
	public function consume_token_response($response){
		# Setup token request parameters:
		$params = array(
			'access_token' 		=> 'access_token',
			'state' 			=> 'state',
			'error'				=> 'error',
			'error_description' => 'error_description'
		);
		$params = apply_filters( 'oauth_token_response', $params );

		# Check for errors
		if(ISSET($response[ $params['error'] ])){
			$description = (ISSET($response[ $params['error_description'] ])) 
				? $response[ $params['error_description'] ] 
				: 'There was an issue retrieving the access token';
			$this->end_login("<strong>ERROR:</strong> ".$description);
		}

		# Basic consumption methods
		$this->set_field('access_token', $response[ $params['access_token'] ]);
		# Set a default 8 hour expiration
		$expiration = apply_filters( 'oauth_token_expiration', time()+8*60*60 );
		$this->set_field('expires', $expiration);

		# Run any provider specific consumption methods
		do_action('consume_token_response', $params, $response);
	}

	/**
	 * Refresh the oauth tokens
	 */
	public function request_refresh_tokens($refresh_token) {
		# Setup refresh token request parameters:
		$params = array(
			'grant_type' 	=> 'refresh_token',
			'refresh_token' => $refresh_token,
			'client_id' 	=> $this->client_id,
			'client_secret' => DIV\services\helper::decrypt($this->client_secret),
		);
		apply_filters( 'oauth__refresh_token_parameters', $params );

		# Attempt curl token request:
		$url = $this->get_token_url();
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
		$result = curl_exec($curl);
		# Check for curl error
		if(curl_errno($curl))
    		$this->end_login('Curl error: '.curl_error($curl));
		curl_close($curl);

		# Parse & handle the result:
		$response = json_decode($result, true);
		return $response;
	}

	/**
	 * Gets the identity
	 */
	private function request_identity() {
		if( ISSET($this->identity_url) ){
			# Setup token request parameters:
			$params = array(
				'access_token' => $this->session['access_token']
			);
			$params = apply_filters( 'oauth_identity_parameters', $params );
			$url_params = http_build_query($params);

			# Attempt curl identity details:
			$url = $this->identity_url.'?'.$url_params;
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
			curl_setopt($curl, CURLOPT_HTTPHEADER, array("Authorization: bearer " . $this->session['access_token']));			
			$result = curl_exec($curl);

			# Check for curl error
			if(curl_errno($curl))
	    		$this->end_login('Curl error: '.curl_error($curl));
			curl_close($curl);

			# Parse & handle the result:
			$response = json_decode($result, true);
			return $response;
		}
		$this->end_login( 'Provider identity url not given to make request to '.$this->provider.' API.');
	}

	/**
	 * Consume identity response and begin user session
	 *
	 * @param    $response
	 */
	private function consume_identity_response($response){
		# Setup token request parameters:
		$params = array(
			'id' 				=> 'id',
			'email' 			=> 'email',
			'error'				=> 'error'
		);
		$params = apply_filters( 'oauth_identity_response', $params );

		# Check for errors
		if(ISSET($response[ $params['error'] ])){
			$this->end_login("<strong>ERROR:</strong> There was an issue retrieving the identity response");
		}

		# Basic consumption methods to set identity
		if( ISSET($response[ $params['id'] ]) )
			$this->set_identity('id', $response[ $params['id'] ]);
		if( ISSET($response[ $params['email'] ]) )
			$this->set_identity('email', $response[ $params['email'] ]);

		# Run any provider specific consumption methods
		do_action('consume_identity_response', $params, $response);
	}

	/** 
	 * Set identity values
	 * 
	 * @param      string  $field
	 * @param      string  $value
	 */ 
	protected function set_identity($field, $value){ 
		$this->identity[$field] = $value;
	} 

	/**
	 * Login to WP with provider identity
	 *
	 * @param      array  $identity
	 */
	private function login_identity(){
		$matched_user = $this->user_match();

		# User match found! Login the user and associate the identity
		if( $matched_user ){
			# Login the matched user
			$this->login_user($matched_user);
			
			# After login, redirect to the user's last location
			$this->end_login("Logged in successfully!", FALSE);
		}
		# Handle the already logged in user if there is one (when linking account from user profile)
		if ( is_user_logged_in() ) {
			# There was a wordpress user logged in, but it is not associated with the now-authenticated
			# user's email address, so associate it now
			global $current_user;
			wp_get_current_user();
			$user_id = $current_user->ID;
			$this->link_identity($user_id);
			# After linking the account, redirect user to their last url
			$this->end_login("Your account was linked successfully with your third party authentication provider.", FALSE);
		}
		# Handle the logged out user or no matching user (register the user)
		if ( !is_user_logged_in() && !$matched_user ) {
			# This person is not logged into a wordpress account and has no third party authentications
			# registered, so proceed to register the wordpress user 
			// include 'register.php';
			$this->end_login('User does not exist and registration is not currently available. Please contact site admin for support.');
		}

	}

	/**
	 * Check for a matching WP user using provider identity
	 * 
	 * @return     WP_User $matched_user
	 */
	function user_match(){
		# Try to find a matching wp user for the now-authenticated user's oauth identity
		$matched_user = $this->match_wp_user($this->identity);

		# No WP user by provider id, search by provider email
		if( empty($matched_user) && ISSET($this->identity) ){
			$matched_user = $this->match_wp_user_by_email($this->identity);

			# If we find a match with this method then we need to link the account
	    	if(!empty($matched_user)) $this->link_identity($matched_user->ID);
		}

		return $matched_user;
	}

	/**
	 * Match the oauth identity to an existing wordpress user account
	 *
	 * @return     WP_User|boolean
	 */
	function match_wp_user() {
		if( ISSET($this->identity['id']) ){
			global $wpdb;
			$usermeta_table = $wpdb->usermeta;
			$query_string = "SELECT $usermeta_table.user_id FROM $usermeta_table WHERE $usermeta_table.meta_key = 'oauth_identity' AND $usermeta_table.meta_value LIKE '%" . $this->provider . "|" . $this->identity['id'] . "%'";
			$query_result = $wpdb->get_var($query_string);
			# Attempt to get a WP user with the matched id:
			$user = get_user_by('id', $query_result);
			return $user;
		}
		return FALSE;
	}

	/**
	 * Check for existing WP user by email
	 *
	 * @return     WP_User|boolean
	 */
	public function match_wp_user_by_email() {
		if( ISSET($this->identity['email']) ){
		    $user = get_user_by('email', $this->identity['email']);
		    return $user;
		}
		return FALSE;
	}

	/**
	 * Links a third-party account to an existing wordpress user account
	 *
	 * @param      string  $user_id
	 */
	function link_identity($user_id) {
		if ( ISSET($this->identity['id']) ) {
			add_user_meta( 
				$user_id, 
				'oauth_identity', 
				$this->provider . '|' . $this->identity['id'] . '|' . time()
			);
		}
	}

	/**
	 * Manually login the provided user
	 *
	 * @param      WP_User  $user
	 */
	function login_user($user){
		$user_id = $user->ID;
		$user_login = $user->user_login;
		wp_set_current_user( $user_id, $user_login );
		wp_set_auth_cookie( $user_id );
		
		# Clear login state
		$this->clear_login_state();

		# Maintain default WP action 
		do_action( 'wp_login', $user_login, $user );

		$this->end_login('Logged in successfully', FALSE);
	}

	/**
	 * Ends the login request by clearing the login state and redirecting
	 * the user to the desired page
	 *
	 * @param      array  $msg
	 */
	function end_login($msg, $error=TRUE) {
		# Unset last url for redirect
		if( ISSET($_SESSION[$this->session_string]["last_url"]) ){
			$last_url = $_SESSION[$this->session_string]["last_url"];
			unset($_SESSION[$this->session_string]["last_url"]);
		} else {
			$last_url = $this->session['last_url'];
		}

		# Clear login state
		$this->clear_login_state();
		
		# Set result for message
		set_transient($_SERVER['REMOTE_ADDR'].'_oauth_notify', $msg, 60);

		# Do not proceed if there is an error
		if($error){
			wp_safe_redirect($last_url);
			die();
		}

		$redirect_method = get_option('options_oauth_redirect');

		$redirect_url = "";
		switch ($redirect_method) {
			case "home_page":
				$redirect_url = site_url();
				break;
			case "last_page":
				$redirect_url = $last_url;
				break;
			case "specific_page":
				$redirect_url = get_permalink(get_option('login_redirect_page'));
				break;
			case "admin_dashboard":
				$redirect_url = admin_url();
				break;
			case "user_profile":
				$redirect_url = get_edit_user_link();
				break;
			case "custom_url":
				$redirect_url = get_option('login_redirect_url');
				break;
		}
		
		wp_safe_redirect($redirect_url);
		die();
	}

/************************************
 * OAuth feature methods
 ************************************/
	
	/**
	 * Default activation method
	 */
	public function activate(){
        # Attempt login by default
        $this->login();
        # Login feature disabled, redirect to home page
        $this->end_login('No features or services available, please contact support');
    }

	/**
	 * Enable SSO login feature
	 */
	public function login(){
        # Enable Single Sign-On
        if(in_array("login", $this->features, TRUE)){
            $this->enable_sso();
        }
    }

	/**
	 * Request user's email to match/login to WP user
	 */
	protected function enable_sso(){
        # Request identity information
        $id_response = $this->request_identity();
        # Consume identity
        $this->consume_identity_response($id_response);
        # Attempt login
        $this->login_identity();
	}

}

?>