<?php

/**
 * Plugin Name: Tippin.me for WordPress- Bitcoin Lightning Donations for WordPress
 * Plugin URI: https://github.com/danielmcclure/tippin.me-for-wordpress
 * Description: Easily integrate Tippin.me links below your WordPress posts to accept Bitcoin Lightning donations.
 * Version: 0.1.0
 * Author: danielmcclure
 * Author URI: https://tippin.me/@danielmcclure
 * Text Domain: tippin-me
*/

/* Settings Config */

class TippinMeSettings {
    // Holds the values to be used in the fields callbacks
    private $options;

    // Start Up
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
    }

    // Add Options Page
    public function add_plugin_page() {
        // This page will be under "Settings"
        add_options_page(
            'Tippin.me', 
            'Tippin.me', 
            'manage_options', 
            'tippin_me_settings_admin', 
            array( $this, 'create_admin_page' )
        );
    }

    // Options page callback
    public function create_admin_page() {
        // Set class property
        $this->options = get_option( 'tippin_me_account' );
        ?>
        <div class="wrap">
            <h1>Tippin.me for WordPress Settings</h1>
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'tippin_me_account_settings' );
                do_settings_sections( 'tippin-me-settings-admin' );
                submit_button();
            ?>
            </form>
        </div>
        <?php
    }

    // Register and add settings
    public function page_init() {        
        register_setting(
            'tippin_me_account_settings', // Option group
            'tippin_me_account', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'tippin_me_account_details', // ID
            'Tippin.me Account Settings', // Title
            array( $this, 'print_section_info' ), // Callback
            'tippin-me-settings-admin' // Page
        );  

        add_settings_field(
            'tippin_me_username', // ID
            'Tippin.me Username:', // Title 
            array( $this, 'tippin_me_username_callback' ), // Callback
            'tippin-me-settings-admin', // Page
            'tippin_me_account_details' // Section           
        ); 
      
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
	public function sanitize( $input ) {
		$new_input = array();
		if( isset( $input['tippin_me_username'] ) )
			$new_input['tippin_me_username'] = sanitize_text_field( $input['tippin_me_username'] );
			$new_input['tippin_me_username'] = preg_replace('/^@/', '', $new_input['tippin_me_username']);
		return $new_input;
	}

	// Print the Section text
	public function print_section_info() {
		print '<p>If you find this plugin useful, please consider sending a lightning tip to <a href="https://tippin.me/@eiprol" target="_blank" rel="noopener noreferrer">@eipro</a> (creator of Tippin.me) and <a href="https://tippin.me/@danielmcclure" target="_blank" rel="noopener noreferrer">@danielmcclure</a> (creator of Tippin.me for WordPress plugin).</p><p>To enable <a href="https://tippin.me/?utm_source=wordpress&utm_campaign=wordpress-plugin&utm_medium=referral" target="_blank" rel="noopener noreferrer">Tippin.me</a> buttons below your WordPress posts, simply enter your account details below...</p>';
	}

	// Get the settings option array and print one of its values
	public function tippin_me_username_callback() {
		printf(
			'<input type="text" id="tippin_me_username" name="tippin_me_account[tippin_me_username]" value="%s" />',
			isset( $this->options['tippin_me_username'] ) ? esc_attr( $this->options['tippin_me_username']) : ''
		);
	}
}

// Generate Settings in Admin View
if( is_admin() )
    $tippin_me_settings = new TippinMeSettings();

/* Core Logic */

function auto_insert_after_post($content){
	if ( is_single() ) {
		$content .= tippin_me_output();
	}
	return $content;
	}
add_filter( "the_content", "auto_insert_after_post" );

function tippin_me_output() {
	$tippin_me_options = get_option( 'tippin_me_account');
	$tippin_me_username = $tippin_me_options['tippin_me_username'];

	if( isset( $tippin_me_options['tippin_me_username'] ) && $tippin_me_options['tippin_me_username'] != '') {
		echo '
		<!-- Beginning of tippin.me Button -->
		<div id="tippin-button" data-dest="'. $tippin_me_username .'"></div>
		<script src="https://tippin.me/buttons/tip.js" type="text/javascript"></script>
		<!-- End of tippin.me Button -->
		';
	} else {
		return false;
	}
}