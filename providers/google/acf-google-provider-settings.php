<?php
/**
 * ACF Fields: Google OAuth settings
 * 		Settings for configuring the Google OAuth connection
 * 		
 * @package   	OAuth
 * @subpackage  Google
 * @author   	Nick Worth
 */
if ( ! defined( 'ABSPATH' ) ) exit;

# Check if provider is enabled
$providers = get_option( 'options_oauth_providers' );
if(is_array($providers)){
	$enabled = in_array("google", $providers, TRUE);
} else{
	$enabled = FALSE;
}
# Setup ACF fields
if( function_exists('acf_add_local_field_group') && $enabled ):

acf_add_local_field_group(array (
	'key' => 'google_provider_settings',
	'title' => 'Google Provider Settings',
	'fields' => array (
		array (
			'key' => 'google_features',
			'label' => 'Features',
			'name' => 'google_features',
			'type' => 'checkbox',
			'instructions' => 'Select the features you want enabled for this provider.',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'choices' => array (
				'login' => '<strong>Login:</strong> provides single sign-on (SSO) to your site',
			),
			'default_value' => array (
				0 => 'login',
			),
			'layout' => 'vertical',
			'toggle' => 0,
			'return_format' => 'value',
		),
		array (
			'key' => 'google_client_id',
			'label' => 'Client Id',
			'name' => 'google_client_id',
			'type' => 'text',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => array (
				array(
					array (
						'field' => 'google_features',
						'operator' => '==',
						'value' => 'login',
					),
				),
			),
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'placeholder' => '566300794424-lp19dagj9kms01d2508chh3iu58v6iam.apps.googleusercontent.com',
			'prepend' => '',
			'append' => '',
			'maxlength' => '',
		),
		array (
			'key' => 'google_client_secret',
			'label' => 'Client Secret',
			'name' => 'google_client_secret',
			'type' => 'password',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => array (
				array(
					array (
						'field' => 'google_features',
						'operator' => '==',
						'value' => 'login',
					),
				),
			),
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'placeholder' => 'iO4dEb-16KbSJE0L0h4_3x2Z',
			'prepend' => '',
			'append' => '',
			'readonly' => 0,
			'disabled' => 0,
		),
		array (
			'key' => 'google_instructions',
			'label' => 'Instructions',
			'name' => '',
			'type' => 'message',
			'instructions' => '<ol>
					<li>Visit the Google website for developers <a href="https://console.developers.google.com/apis/dashboard" target="_blank">console.developers.google.com</a>.</li>
					<li>At Google, create a new Project and enable the Google+ API. This will enable your site to access the Google+ API.</li>
					<li>At Google, provide your site\'s homepage URL (<?php echo $blog_url; ?>) for the new Project\'s Redirect URI. Don\'t forget the trailing slash!</li>
					<li>At Google, you must also configure the Consent Screen with your Email Address and Product Name. This is what Google will display to users when they are asked to grant access to your site/app.</li>
					<li>Paste your Client ID/Secret provided by Google into the fields above.</li>
				</ol>',
			'required' => 0,
			'conditional_logic' => array (
				array(
					array (
						'field' => 'google_features',
						'operator' => '==',
						'value' => 'login',
					),
				),
			),
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
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