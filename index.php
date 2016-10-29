<?php 
/**
 * OAuth Add-on
 * 		Get authentication through 3rd party services using the OAuth 2.0
 * 		protocol to establich sessons and identity
 * 		NOTE: Depends on ACF 5.0+ and Div Library plugins
 * 
 * @package 	OAuth
 * @author 	   	Nick Worth
 * @version     1.0
 * @link        http://divblend.com/div-starter/add-ons/oauth/
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class OAuthAddon {

	# OAuth addon version 
	public $version = '0.1.0';

	public $providers = array(
		'salesforce'	=> 'Salesforce',
		'google'		=> 'Google',
		'outlook'		=> 'Outlook'
	);

	/**
	 * Enabled active providers
	 * @var array
	 */
	public $active_providers;

	/**
	 * Current provider
	 * @var array
	 */
	public $provider;

	/**
	 * Current OAuth object
	 * @var array
	 */
	public $oauth;

	/**
	 * OAuthAddon Constructor.
	 */
	public function __construct() {
		# Include scripts
		$this->includes();
		# Configure the connector
		$this->init();
		# Setup active providers
		$this->active_providers = $this->get_active_providers();
	}

	/**
	 * OAuth Configuration
	 */
	private function includes(){
		# Install OAuth framework
		require_once(__DIR__.'/class-oauth-provider.php');
		# Install OAuth settings
		require_once(__DIR__.'/acf-oauth-settings.php');
		# Install active providers
		foreach($this->providers as $provider){
			$this->install_provider( strtolower($provider) );
		}
	}

	/**
	 * Salesforce Connector init
	 */
	private function init(){
		# Custom CSS ACF fields
		add_action('acf/input/admin_head', array( $this, 'css') );
		# Customize the login page
		add_filter('login_message', array( $this, 'customize_login_screen') );
		# Hook scripts and styles for login page:
		add_action('login_enqueue_scripts', array( $this, 'init_scripts_styles') );
		add_action('admin_enqueue_scripts', array( $this, 'init_scripts_styles') );

		# Register endpoints
		// add_action( 'init', array( $this, 'endpoints') );
		
		# Hook the query_vars and template_redirect so we can stay within the wordpress context no matter what (avoids having to use wp-load.php)
		add_filter( 'query_vars', array( $this, 'add_query_vars') );
		add_action( 'template_redirect', array( $this, 'query_var_handler') );
		# Register script redirections
		add_filter( 'rewrite_rules_array', array( $this, 'create_rewrite_rules') );
		add_filter( 'template_include', array( $this, 'intercept_template'), 100 );
		// add_filter( 'wp_loaded', array( $this, 'flushRewriteRules'));
		
		# Add error catching
		add_action('wp_footer', array( $this, 'notify' ) );
		add_filter('admin_footer', array($this, 'notify') );
		add_filter('login_footer', array($this, 'notify') );

		# User profile
		add_action('show_user_profile', array($this, 'linked_accounts'));
		// add_action('admin_enqueue_scripts', array($this, 'admin_scripts') );
		add_action('wp_ajax_oauth_unlink_account', array($this, 'unlink_account'));
		add_action('wp_ajax_nopriv_oauth_unlink_account', array($this, 'unlink_account'));
	}

	/**
	 * Init scripts and styles for use on login page
	 */
	function init_scripts_styles() {
		# Localize php variables, making them available as a JS variable in the browser:
		$oauth_vars = array(
			# Basic info:
			'ajaxurl' => admin_url('admin-ajax.php'),
			'template_directory' => get_bloginfo('template_directory'),
			'stylesheet_directory' => get_bloginfo('stylesheet_directory'),
			'plugins_url' => plugins_url(),
			'plugin_dir_url' => plugin_dir_url(__FILE__),
			'url' => get_bloginfo('url'),
			'logout_url' => wp_logout_url(),
			# Other:
			'hide_login_form' => get_option('options_allow_wordpress_login'),
			// 'show_login_messages' => get_option('oauth_show_login_messages'),
			// 'logout_inactive_users' => get_option('oauth_logout_inactive_users'),
			'logged_in' => is_user_logged_in(),
		);
		wp_enqueue_script('oauth-vars', plugins_url('/js/oauth-vars.js', __FILE__), array('jquery'));
		wp_localize_script('oauth-vars', 'oauth_vars', $oauth_vars);
		# Load the core plugin scripts/styles:
		wp_enqueue_script('oauth-script', plugin_dir_url( __FILE__ ) . 'js/oauth.js', array('jquery'));
		wp_enqueue_style('oauth-style', plugin_dir_url( __FILE__ ) . 'css/oauth.css');
	}

	/**
	 * Enque admin scripts
	 */
	function admin_scripts(){
		wp_enqueue_script('oauth-script', plugin_dir_url( __FILE__ ) . 'js/oauth-admin.js', array('jquery'));
	}

	/**
	 * Gets the active providers.
	 *
	 * @return     array
	 */
	function get_active_providers(){
		$active_providers = get_option('options_oauth_providers');
		if(is_array($active_providers)) return $active_providers;
		return array();
	}

	/**
	 * Load the provider scripts that is being requested by the user
	 * or being called back after authentication
	 *
	 * @param      string  $provider
	 */
	function install_provider($provider) {
		# Install provider script
		require_once(__DIR__.'/providers/'.$provider.'/oauth-'.$provider.'.php');	// TODO: Autoload provider class (only called when attempting oauth dance)
		# Install provider settings
		require_once(__DIR__.'/providers/'.$provider.'/acf-'.$provider.'-provider-settings.php');
		# Encrypt password fields upon database entry
		add_filter('acf/update_value/name='.$provider.'_client_secret', array( $this, 'encrypt_ACF_password_fields'), 10, 3);
	}

	/**
	 * Show a custom login form on the default login screen
	 */
	function customize_login_screen() {
		$html = "";
		if (is_user_logged_in()) {
			$html .= "<a class='oauth-logout-button' href='" . wp_logout_url() . "' title='Logout'>Logout</a>";
		} else {
			$html .= '<div class="oauth-login-form oauth-layout-buttons-column oauth-layout-align-center"> <nav>
				<p id="oauth-title">Please login:</p>';
				$html .= $this->login_buttons();
			$html .= '</nav></div>';
		}
		echo $html;
	}

	/**
	 * Generate and return the login buttons, depending on available providers
	 *
	 * @return     string
	 */
	function login_buttons() {
		# Generate the atts once (cache them), so we can use it for all buttons without computing them each time:
		$site_url = get_bloginfo('url');
		# Check for SSL
		if( force_ssl_admin() ) { $site_url = set_url_scheme( $site_url, 'https' ); }
		# Setup redirection if passed in URL
		$redirect_to = isset($_GET['redirect_to']) ? urlencode($_GET['redirect_to']) : '';
		if ($redirect_to) {$redirect_to = "&redirect_to=" . $redirect_to;}
		
		// TODO: Add icons

		$atts = array(
			'site_url' => $site_url,
			'redirect_to' => $redirect_to,
		);

		# Generate the login buttons for available providers:
		$html = "";
		foreach ($this->providers as $slug => $name) {
			if(in_array($slug, $this->active_providers, TRUE))
				$html .= $this->login_button($slug, $name, $atts);
		}
		return $html;
	}

	/**
	 * Generates and returns a login button for a specific provider
	 *
	 * @param      string  $provider
	 * @param      string  $display_name
	 * @param      array  $atts
	 *
	 * @return     string
	 */
	function login_button($provider, $display_name, $atts) {
		$html = "";
			$html .= "<a id='login-" . $provider . "' class='oauth-login-button' href='" . $atts['site_url'] . "?connect=" . $provider . $atts['redirect_to'] . "'>";
			$html .= $display_name;
			$html .= "</a>";
		return $html;
	}

	/**
	 * Adds query variables
	 *
	 * @param      string  $vars
	 *
	 * @return     array
	 */
    function add_query_vars($vars) {
        $vars[] = 'connect'; 	# Used for beginning authentication
        $vars[] = 'oauth';		# Used for create an "/oauth/{provider}" endpoint
        return $vars;
    }

    /**
	 * Handle the querystring triggers
	 */
	function query_var_handler() {
		if (get_query_var('connect')) {
			$this->provider = get_query_var('connect');
			$this->start_handshake($this->provider);
		}
	}

	function start_handshake($provider){
		// TODO: autolaod provider script
		
		# Dynamically load provider class
		$class = "OAuth_".ucfirst($provider);
		$this->oauth = new $class();

		# Step 1: Get authorization code
		$this->oauth->request_authorization_code();
	}

	/**
     * Set endpoints for oauth scripts
     */
	function endpoints() {
		add_rewrite_endpoint( 'oauth', EP_ATTACHMENT | EP_PAGES );
	}

	/**
	 * Creates a rewrite rules
	 *
	 * @param      array  $rules
	 *
	 * @return     array
	 */
	function create_rewrite_rules($rules) {
		global $wp_rewrite;
        $newRule = array('oauth/(.+)' => 'index.php?oauth=' . $wp_rewrite->preg_index( 1 ) );
        $newRules = $newRule + $rules;
        return $newRules;
	}

	/**
	 * Flush the rewrite rules
	 */
	function flushRewriteRules() {
		# Check to see if the main rewrite is used and skip if needed.
		$rules = get_option( 'rewrite_rules' );
		if( isset( $rules['oauth/(.+)'] ) )
		    return;
		global $wp_rewrite;
			$wp_rewrite->flush_rules();
    }

	/**
	 * Redirect caught query vars to script templates
	 *
	 * @param      string  $template
	 *
	 * @return     string
	 */
	function intercept_template( $template ) {
		global $wp_query;
		if ( isset( $wp_query->query_vars['oauth'] ) ) {
			require_once dirname( __FILE__ ) . '/callback.php';
			exit;
		}

		return $template;
	}

	/**
	 * Encrypt ACF password fields
	 *
	 * @param      string  $value
	 * @param      string  $post_id
	 * @param      string  $field
	 *
	 * @return     string
	 */
	public function encrypt_ACF_password_fields( $value, $post_id, $field ){
		# Attempt to decrypt in case encrypted value was passed
	    $value = DIV\services\helper::decrypt( $value );  
		# Encrypt value before database insert
	    $value = DIV\services\helper::encrypt( $value );  
	    return $value;
	}

	/**
	 * Pushes login messages into the dom where they can be extracted by javascript
	 */
	function notify() {
		$msg = get_transient( $_SERVER['REMOTE_ADDR'].'_oauth_notify' );
		if($msg){
			_e("<script type='text/javascript'>OAUTH.notify('".$msg."')</script>");
			delete_transient( $_SERVER['REMOTE_ADDR'].'_oauth_notify' );
		}
	}

	/**
	 * Add custom CSS to ACF fields
	 */
	public function css() {
	    _e('<style type="text/css">
	 
	    	.header.acf-field-message{
	        	background: #eee;
	        	border: 1px solid #999 !important;
    			text-align: center;
	        }
	        .header.acf-field-message p{
	        	font-style: italic;
	        }
	    	.disabled input {
	        	pointer-events: none;
    			tab-index: -1;
    			background: #eee;
	        }
	        .oauth-panel.disabled input {
    			background: #ccc;
	        }
	        .oauth-panel{
	        	border: 0px !important;
	        	background: #333;
	        }
	        .oauth-panel .acf-label{
	        	color: #fff;
	        }
	        .acf-field-57e30d73143e8{
	        	text-align: center;
	        }
	        .acf-field-57e30d73143e8 label{
	        	font-size: 18px;
	        }
	 
	    </style>');
	}

	/**
	 * Shows the user's linked providers, used on the 'Your Profile' page
	 */
	function linked_accounts() {
		# Get the current user:
		global $current_user;
		wp_get_current_user();
		$user_id = $current_user->ID;
		# Get the oauth_identity records:
		global $wpdb;
		$usermeta_table = $wpdb->usermeta;
		$query_string = "SELECT * FROM $usermeta_table WHERE $user_id = $usermeta_table.user_id AND $usermeta_table.meta_key = 'oauth_identity'";
		$query_result = $wpdb->get_results($query_string);
		# List the oauth_identity records:
		echo "<div id='oauth-linked-accounts'>";
		echo "<h3>Linked Accounts</h3>";
		echo "<p>Manage the linked accounts which you have previously authorized to be used for logging into this website.</p>";
		echo "<table class='form-table'>";
		echo "<tr valign='top'>";
		echo "<th scope='row'>Your Linked Providers</th>";
		echo "<td>";
		if ( count($query_result) == 0) {
			echo "<p>You currently don't have any accounts linked.</p>";
		}
		echo "<div class='oauth-linked-accounts'>";
		foreach ($query_result as $oauth_row) {
			$oauth_identity_parts = explode('|', $oauth_row->meta_value);
			$oauth_provider = ucfirst( $oauth_identity_parts[0] );
			$oauth_id = $oauth_identity_parts[1]; // keep this private, don't send to client
			$time_linked = $oauth_identity_parts[2];
			$local_time = strtotime("+" . get_option('gmt_offset') . ' hours', $time_linked);
			echo "<div>" . $oauth_provider . " on " . date('F d, Y h:i A', $local_time) . " <a class='oauth-unlink-account' data-oauth-identity-row='" . $oauth_row->umeta_id . "' href='#'>Unlink</a></div>";
		}
		echo "</div>";
		echo "</td>";
		echo "</tr>";
		echo "<tr valign='top'>";

// TODO: Link another provider feature
		# Feature: Link Provider
		// echo "<th scope='row'>Link Another Provider</th>";
		// echo "<td>";
			
		// 	# OAuth Connect Buttons
		// 	$html = '<div class="oauth-login-form oauth-layout-buttons-column oauth-layout-align-center"> <nav>
		// 		<p id="oauth-title">Connect an Account:</p>';
		// 		$html .= $this->login_buttons();
		// 	$html .= '</nav></div>';
		// 	echo $html;

		// echo "</td>";
		// echo "</td>";
		
		echo "</table>";
	}

	/**
	 * Unlinks a third-party provider from an existing wordpress user account
	 */
	function unlink_account() {
		# Get oauth_identity row index that the user wishes to unlink:
		$oauth_identity_row = $_POST['oauth_identity_row']; // SANITIZED via $wpdb->prepare()
		# Get the current user:
		global $current_user;
		wp_get_current_user();
		$user_id = $current_user->ID;
		# Delete the oauth_identity record from the wp_usermeta table:
		global $wpdb;
		$usermeta_table = $wpdb->usermeta;
		$query_string = $wpdb->prepare("DELETE FROM $usermeta_table WHERE $usermeta_table.user_id = $user_id AND $usermeta_table.meta_key = 'oauth_identity' AND $usermeta_table.umeta_id = %d", $oauth_identity_row);
		$query_result = $wpdb->query($query_string);
		# Notify client of the result;
		if ($query_result) {
			echo json_encode( array('result' => 1) );
		}
		else {
			echo json_encode( array('result' => 0) );
		}
		# wp-ajax requires death:
		die();
	}

}

if( class_exists('OAuthAddon') ) new OAuthAddon();

?>