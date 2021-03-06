<?php
/**
 * OAuth provider class: Google
 *      Using the OAuth class to establish authentication and identity
 *      details from a given provider
 *      
 * @package     OAuth
 * @author      Nick Worth
 */
if ( ! defined( 'ABSPATH' ) ) exit;
 
class OAuth_Google extends OAuthProvider{

    # Provider constants
    const PROVIDER = 'google';

    # Provider settings
    protected $features;
    protected $scope;
    
    /**
     * Provider Constructor
     */
    public function __construct() {
        # Configure the OAuthProvider
        $this->init();
        # Register any provider specific filters
        $this->filters();
        # Register any provider specific actions
        $this->actions();
    }
 
    function init(){
        # Provider settings
        $this->provider         = $this::PROVIDER;
        $this->features         = (get_option('options_google_features')) ? get_option('options_google_features') : array();
        $this->scope            = 'https://www.googleapis.com/auth/plus.profile.emails.read';
        
        # Provider authentication
        $this->client_id        = get_option('options_'.$this->provider.'_client_id');
        $this->client_secret    = get_option('options_'.$this->provider.'_client_secret');
        $this->redirect_uri     = get_bloginfo('url').'/oauth/'.$this->provider;
        
        # OAuth URLs
        $this->auth_url         = 'https://accounts.google.com/o/oauth2/auth'; 
        $this->tokens_url       = 'https://accounts.google.com/o/oauth2/token';
        $this->identity_url     = 'https://www.googleapis.com/plus/v1/people/me';

        # Install provider (DO NOT REMOVE)
        parent::install();
    }
    
    /**
     * Hook into any filters specific to this provider (optional)
     * 
     * NOTE: This is only necessary if you are modifying the 
     *      default values of parameters used throughout the
     *      OAuth process
     */
    function filters(){
        add_filter( 'oauth_authorization_parameters', array( $this, 'authorization_parameters'), 1, 1 );
        // add_filter( 'oauth_token_parameters', array( $this, 'token_parameters'), 1, 1 );
        add_filter( 'oauth_token_response', array( $this, 'token_response'), 1, 1 );
        // add_filter( 'oauth_identity_parameters', array( $this, 'identity_parameters'), 1, 1 );
        // add_filter( 'oauth_identity_response', array( $this, 'identity_response'), 1, 1 );
    }

        /**
         * Modify/extend the authorization parameters
         *
         * @param      array  $params
         */
        public function authorization_parameters($params){
            $params['scope'] = $this->scope;
            return $params;
        }

        /**
         * Modify/extend the token response parameters
         *
         * @param      array  $params
         */
        public function token_response($params){
            $params['token_type']   = 'token_type';
            $params['expires_in']   = 'expires_in';
            $params['id_token']     = 'id_token';
            return $params;
        }

        /**
         * Modify/extend the identity response parameters
         * 
         * @param      array  $params
         */
        public function identity_response($params){
            return $params;
        }

    /**
     * Hook into any actions specific to this provider (optional)
     * 
     * NOTE: This is only necessary if you are modifying the 
     *      default values of parameters used throughout the
     *      OAuth process
     */
    function actions(){
        add_action( 'consume_token_response', array( $this, 'consume_token'), 1, 2 );
        add_action( 'consume_identity_response', array( $this, 'consume_identity'), 1, 2 );
    }

        /**
         * Setup custom token consumption methods
         *
         * @param      array  $params
         * @param      array  $response
         */
        public function consume_token($params, $response){}

        /**
         * Setup custom identity consumption methods
         *
         * @param      array  $params
         * @param      array  $response
         */
        public function consume_identity($params, $response){
            $this->set_identity('email', $response['emails'][0]['value'] );
        }

/************************************
 * Provider specific private methods
 ************************************/

}

?>