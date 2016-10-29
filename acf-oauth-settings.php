<?php
/**
 * ACF Fields: OAuth settings
 * 		Settings for configuring the OAuth addon
 * 		
 * @package   	OAuth
 * @subpackage  Google
 * @author   	Nick Worth
 */
if ( ! defined( 'ABSPATH' ) ) exit;

# Setup ACF Fields
if( function_exists('acf_add_local_field_group') ):

acf_add_local_field_group(array (
	'key' => 'group_57ed7debaff95',
	'title' => 'OAuth Settings',
	'fields' => array (
		array (
			'key' => 'oauth_providers',
			'label' => 'Providers',
			'name' => 'oauth_providers',
			'type' => 'checkbox',
			'instructions' => 'Select all providers that should be enabled for this site',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'choices' => array (
				'google' => 'Google',
				'outlook' => 'Outlook 365',
				'salesforce' => 'Salesforce',
			),
			'default_value' => array (
			),
			'layout' => 'horizontal',
			'toggle' => 0,
			'return_format' => 'value',
		),
		array (
			'key' => 'oauth_redirect',
			'label' => 'Authentication Redirection',
			'name' => 'oauth_redirect',
			'type' => 'radio',
			'instructions' => 'Select where you want user to be redirect after a successful authentication (this can be overriden by specific features)',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'choices' => array (
				'home_page' 		=> 'Home Page',
				'last_page' 		=> 'Last Page',
				'specific_page' 	=> 'Specific Page',
				'admin_dashboard' 	=> 'Admin Dashboard',
				'user_profile' 		=> 'User Profile',
				'custom_url' 		=> 'Custom Url',
			),
			'default_value' => array (
				'last_page'
			),
			'layout' => 'horizontal',
			'toggle' => 0,
			'return_format' => 'value',
		),
		array (
			'key' => 'oauth_redirect_page',
			'label' => 'Custom Redirect Page',
			'name' => 'oauth_redirect_page',
			'type' => 'text',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => array (
				array(
					array (
						'field' => 'oauth_redirect',
						'operator' => '==',
						'value' => 'specific_page',
					),
				),
			),
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'placeholder' => '/my-page',
			'prepend' => '',
			'append' => '',
			'maxlength' => '',
		),
		array (
			'key' => 'oauth_redirect_url',
			'label' => 'Custom Redirect URL',
			'name' => 'oauth_redirect_url',
			'type' => 'text',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => array (
				array(
					array (
						'field' => 'oauth_redirect',
						'operator' => '==',
						'value' => 'custom_url',
					),
				),
			),
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'placeholder' => 'http://domain.com/some-url',
			'prepend' => '',
			'append' => '',
			'maxlength' => '',
		),
		array (
			'key' => 'allow_wordpress_login',
			'label' => 'Allow WordPress Login',
			'name' => 'allow_wordpress_login',
			'type' => 'radio',
			'instructions' => 'If SSO providers are enabled, do you still want the default WordPress login functionality enabled?',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'choices' => array (
				'yes' => 'Yes',
				'no' => 'No',
			),
			'allow_null' => 0,
			'other_choice' => 0,
			'save_other_choice' => 0,
			'default_value' => '',
			'layout' => 'horizontal',
			'return_format' => 'value',
		),
		array (
			'key' => 'oauth_login_logo',
			'label' => 'Login Logo',
			'name' => 'login_logo',
			'type' => 'image',
			'instructions' => 'Select the logo image you want to override the default WordPress image',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'return_format' => 'url',
			'preview_size' => 'medium',
			'library' => 'all',
			'min_width' => '',
			'min_height' => '',
			'min_size' => '',
			'max_width' => '',
			'max_height' => '',
			'max_size' => '',
			'mime_types' => '',
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
	'menu_order' => 0,
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