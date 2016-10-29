<?php
/**
 * ACF Fields: Outlook OAuth settings
 * 		Settings for configuring the Outlook OAuth connection
 * 		
 * @package   	OAuth
 * @subpackage  Outlook
 * @author   	Nick Worth
 */
if ( ! defined( 'ABSPATH' ) ) exit;

# Check if provider is enabled
$providers = get_option( 'options_oauth_providers' );
if(is_array($providers)){
	$enabled = in_array("outlook", $providers, TRUE);
} else{
	$enabled = FALSE;
}
# Setup ACF fields
if( function_exists('acf_add_local_field_group') && $enabled ):

acf_add_local_field_group(array (
	'key' => 'outlook_provider_settings',
	'title' => 'Outlook Provider Settings',
	'fields' => array (
		array (
			'key' => 'outlook_features',
			'label' => 'Features',
			'name' => 'outlook_features',
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
			'key' => 'outlook_tenant',
			'label' => 'Tenant',
			'name' => 'outlook_tenant',
			'type' => 'text',
			'instructions' => 'Used to control who can sign into the application. <a href="https://azure.microsoft.com/en-us/documentation/articles/active-directory-protocols-oauth-code/#_request-an-authorization-code" target="_blank">More Details</a>',
			'required' => 0,
			'conditional_logic' => array (
				array(
					array (
						'field' => 'outlook_features',
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
			'default_value' => 'common',
			'placeholder' => '',
			'prepend' => '',
			'append' => '',
			'maxlength' => '',
		),
		array (
			'key' => 'outlook_client_id',
			'label' => 'Application Id',
			'name' => 'outlook_client_id',
			'type' => 'text',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => array (
				array(
					array (
						'field' => 'outlook_features',
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
			'placeholder' => '1647ae99-9d40-44ac-a13d-65f249f41861',
			'prepend' => '',
			'append' => '',
			'maxlength' => '',
		),
		array (
			'key' => 'outlook_client_secret',
			'label' => 'Client Secret',
			'name' => 'outlook_client_secret',
			'type' => 'password',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => array (
				array(
					array (
						'field' => 'outlook_features',
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
			'placeholder' => 'E1AFXgMwaCqd3Fmw994F7zD',
			'prepend' => '',
			'append' => '',
			'readonly' => 0,
			'disabled' => 0,
		),
		array (
			'key' => 'outlook_instructions',
			'label' => 'Instructions',
			'name' => '',
			'type' => 'message',
			'instructions' => '<ol>
					<li>Register as an Outlook Developer at <a href="https://apps.dev.microsoft.com/" target="_blank">apps.dev.microsoft.com</a>.</li>
					<li>At Application Registration Portal, click "Add an app". This will enable your site to access the Outlook REST API.</li>
					<li>Within your application, click "Add Platform" and add a "Web" platform providing your site\'s homepage URL (<?php echo $blog_url; ?>) for the new App\'s Redirect URI(s). Don\'t forget the trailing slash!</li>
					<li>Paste your Application ID) and Secret provided by Application Registration Portal into the fields above</li>
					<li>For more details <a href="https://azure.microsoft.com/en-us/documentation/articles/active-directory-v2-app-registration/">checkout the documenation</a>
				</ol>',
			'required' => 0,
			'conditional_logic' => array (
				array(
					array (
						'field' => 'outlook_features',
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