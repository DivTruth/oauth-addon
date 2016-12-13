<?php
/**
 * ACF Fields: Salesforce OAuth settings
 * 		Settings for configuring the Salesforce OAuth connection
 * 		
 * @package   	OAuth
 * @subpackage  Salesforce
 * @author   	Nick Worth
 */
if ( ! defined( 'ABSPATH' ) ) exit;

# Check for site admin logout request
if(ISSET($_REQUEST['site_admin']) && $_REQUEST['site_admin'] == 'logoff'){
	OAuth_Salesforce::clear_site_admin();
}

# Check if provider is enabled
$providers = get_option( 'options_oauth_providers' );
if(is_array($providers)){
	$enabled = in_array("salesforce", $providers, TRUE);
} else{
	$enabled = FALSE;
}
# Setup login button data
if( function_exists('acf_add_local_field_group') && $enabled ):
$atts = array(
	'site_url' 		=> get_bloginfo('url'),
	'redirect_to' 	=> $_SERVER['REQUEST_URI'],
	'state'			=> 'site_admin'
);
# Login button
$site_admin_login = '<div style="text-align:center; margin:30px 0 10px;">'.OAuthAddon::login_button("salesforce", 'Sign In', $atts).'</div>';
# Logoff button
$site_admin_logoff = '<div style="text-align:center; margin:30px 0 10px;"><a id="logout-salesforce" class="oauth-login-button" href="'.$_SERVER['REQUEST_URI'].'&site_admin=logoff" onclick="">Sign Out</a></div>';
# Determine which button should be displayed
if(!OAuth_Salesforce::has_site_admin_session()){
	$site_admin_button = $site_admin_login;
} else {
	$site_admin_button = $site_admin_logoff;
}

# Setup ACF fields
acf_add_local_field_group(array (
	'key' => 'group_57ed757529b25',
	'title' => 'Saleforce Provider Settings',
	'fields' => array (
		array (
			'key' => 'field_57ed75f491739',
			'label' => 'Features',
			'name' => 'salesforce_features',
			'type' => 'checkbox',
			'instructions' => 'Select the features you want enabled for this provider.',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '60',
				'class' => '',
				'id' => '',
			),
			'choices' => array (
				'login' => '<strong>Login:</strong> provides single sign-on (SSO) to your site',
				'site-admin' => '<strong>Site Admin:</strong> establishes an admin session on your site that can be used by other features or application that need to access data from your provider',
			),
			'default_value' => array (
				0 => 'login',
			),
			'layout' => 'vertical',
			'toggle' => 0,
			'return_format' => 'value',
		),
		array (
			'key' => 'field_57d84d412380a',
			'label' => 'Salesforce Environment',
			'name' => 'salesforce_environment',
			'type' => 'radio',
			'instructions' => 'Select the environment type to connect to. Note, must have API access',
			'required' => 1,
			'conditional_logic' => array (
				array(
					array (
						'field' => 'field_57ed75f491739',
						'operator' => '==',
						'value' => 'login',
					),
				),
				array (
					array (
						'field' => 'field_57ed75f491739',
						'operator' => '==',
						'value' => 'site-admin',
					),
				),
			),
			'wrapper' => array (
				'width' => '40',
				'class' => '',
				'id' => '',
			),
			'choices' => array (
				'sandbox' => 'Sandbox',
				'production' => 'Production',
			),
			'allow_null' => 0,
			'other_choice' => 0,
			'save_other_choice' => 0,
			'default_value' => 'sandbox',
			'layout' => 'vertical',
			'return_format' => 'value',
		),
		array (
			'key' => 'field_57ed758391736',
			'label' => 'Client Id',
			'name' => 'salesforce_client_id',
			'type' => 'text',
			'instructions' => 'Get from salesforce <a href="https://login.salesforce.com/?retURL=02u">Remote Access Quicklink</a>',
			'required' => 0,
			'conditional_logic' => array (
				array(
					array (
						'field' => 'field_57ed75f491739',
						'operator' => '==',
						'value' => 'login',
					),
				),
				array (
					array (
						'field' => 'field_57ed75f491739',
						'operator' => '==',
						'value' => 'site-admin',
					),
				),
			),
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'placeholder' => 'ym9PkpnXQ1pBxT246RbkPYMap',
			'prepend' => '',
			'append' => '',
			'maxlength' => '',
		),
		array (
			'key' => 'field_57ed75a891737',
			'label' => 'Client Secret',
			'name' => 'salesforce_client_secret',
			'type' => 'password',
			'instructions' => 'Get from salesforce <a href="https://login.salesforce.com/?retURL=02u">Remote Access Quicklink</a>',
			'required' => 0,
			'conditional_logic' => array (
				array(
					array (
						'field' => 'field_57ed75f491739',
						'operator' => '==',
						'value' => 'login',
					),
				),
				array (
					array (
						'field' => 'field_57ed75f491739',
						'operator' => '==',
						'value' => 'site-admin',
					),
				),
			),
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'placeholder' => '',
			'prepend' => '',
			'append' => '',
			'readonly' => 0,
			'disabled' => 0,
		),
		array (
			'key' => 'field_57ed7c737fe4f',
			'label' => 'Connect a "Site Administrator" to your Salesforce environment using OAuth 2.0',
			'name' => '',
			'type' => 'message',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => array (
				array (
					array (
						'field' => 'field_57ed75f491739',
						'operator' => '==',
						'value' => 'site-admin',
					),
				),
			),
			'wrapper' => array (
				'width' => '',
				'class' => 'oauth-panel',
				'id' => '',
			),
			'message' => $site_admin_button,
			'new_lines' => 'wpautop',
			'esc_html' => 0,
		),
		array (
			'key' => 'field_57ed79c38e9a1',
			'label' => 'Authorization Code',
			'name' => 'salesforce_authorization_code',
			'type' => 'text',
			'instructions' => 'The authorization token is used to request an access and refresh token',
			'required' => 0,
			'conditional_logic' => array (
				array (
					array (
						'field' => 'field_57ed75f491739',
						'operator' => '==',
						'value' => 'site-admin',
					),
				),
			),
			'wrapper' => array (
				'width' => '',
				'class' => 'disabled oauth-panel',
				'id' => '',
			),
			'default_value' => '',
			'placeholder' => '',
			'prepend' => '',
			'append' => '',
			'maxlength' => '',
		),
		array (
			'key' => 'field_57ed79788e9a0',
			'label' => 'Access Token',
			'name' => 'salesforce_access_token',
			'type' => 'text',
			'instructions' => 'This token is used to grant access during an API call',
			'required' => 0,
			'conditional_logic' => array (
				array (
					array (
						'field' => 'field_57ed75f491739',
						'operator' => '==',
						'value' => 'site-admin',
					),
				),
			),
			'wrapper' => array (
				'width' => '',
				'class' => 'disabled oauth-panel',
				'id' => '',
			),
			'default_value' => '',
			'placeholder' => '',
			'prepend' => '',
			'append' => '',
			'maxlength' => '',
		),
		array (
			'key' => 'field_57ed79eb8e9a2',
			'label' => 'Refresh Token',
			'name' => 'salesforce_refresh_token',
			'type' => 'text',
			'instructions' => 'This token is used to refresh the session if it expires by requesting a new access and refresh token',
			'required' => 0,
			'conditional_logic' => array (
				array (
					array (
						'field' => 'field_57ed75f491739',
						'operator' => '==',
						'value' => 'site-admin',
					),
				),
			),
			'wrapper' => array (
				'width' => '',
				'class' => 'disabled oauth-panel',
				'id' => '',
			),
			'default_value' => '',
			'placeholder' => '',
			'prepend' => '',
			'append' => '',
			'maxlength' => '',
		),
	),
	'location' => array (
		array (
			array (
				'param' => 'options_page',
				'operator' => '==',
				'value' => 'site-settings',
			),
		),
	),
	'menu_order' => 1,
	'position' => 'normal',
	'style' => 'default',
	'label_placement' => 'top',
	'instruction_placement' => 'label',
	'hide_on_screen' => '',
	'active' => 1,
	'description' => '',
));

endif;
?>