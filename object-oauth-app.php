<?php 
/**
 * OAuth Application object class
 *      This is the OAuth application base class for defining
 *      the OAuth implementation 
 *      
 * @package     OAuth
 * @author      Nick Worth
 */
if ( ! defined( 'ABSPATH' ) ) exit;

abstract class OAuthApp {

    /**
     * Define application settings
     */
    protected $app;
    protected $provider;
    protected $url;
    protected $client_id;
    protected $client_secret;
    protected $login_uri;

    /**
     * Authentiation object
     *
     * @var        OAuth_Salesforce
     */
    public $oauth;

    /**
     * OAuthApp Constructor
     */
    function __construct() {
        $this->config_oauth();
    }

    /**
     * Initialize OAuth app
     */
    public function init(){
        $this->get_user_session();
    }

    /**
     * Setup OAuth with app-specific settings
     */
    protected function config_oauth(){
        # Create a Salesforce authenticatio object
        $class = "OAuth_".ucfirst($this->provider);
        $this->oauth = new $class();

        # Install the application
        $this->oauth->install_app($this->app);

        # Configure the app's settings
        $this->oauth->client_id       = $this->client_id; 
        $this->oauth->client_secret   = $this->client_secret; 
        $this->oauth->login_uri       = $this->login_uri; 
        # Remove from settings from app now that they are stored in the oauth object
        unset($this->app);
        unset($this->provider);
        unset($this->client_id);
        unset($this->client_secret);
        unset($this->login_uri);
    }

    public function get_user_session(){
        # First, do we already have a user session
        if( !$this->hasSession() ){
            # If not begin the oauth handshaking process
            $this->oauth->request_authorization_code();
        }
    }

    /**
     * Determines if it has session.
     *
     * @return     boolean
     */
    private function hasSession(){
        if( ! ISSET($_SESSION[$this->oauth->session_string]['access_token']) ) return FALSE;
        return TRUE;
    }

    /**
     * Activation description
     */
    public function activate(){
        if($this->url != '')
        header("Location: $this->url");
        exit;
    }

    /**
     * Allow response to be parsed
     *
     * @param      array  $response
     */
    protected function parse($response){}
}

?>