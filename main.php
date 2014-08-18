<?php
/*
Plugin Name: Static Wordpress
Plugin URI: https://github.com/DrSpaceMonkey/static_wordpress/
Description: Generates a static HTML version of your Wordpress site
Author: Steven Allen
Version: 1.0
Author URI: https://github.com/DrSpaceMonkey/
*/


class StaticWordpress {

	private $wp_query = NULL;
	protected $database = NULL;
	private $wpsf;
	private $plugin_name = 'Static Wordpress';
	
	
	public function __construct() {
		register_activation_hook( __FILE__, array( 'StaticWordpress', 'activate' ) );
		include_once dirname(__FILE__) . '/web_interface.php';
          include_once dirname(__FILE__) . '/database_interface.php';
		require_once dirname(__FILE__) . '/wp-settings-framework.php';
          $this->wpsf = new WordPressSettingsFramework( dirname(__FILE__) . '/settings/settings.php', '' );
		add_action( 'admin_menu', array(&$this, 'admin_menu'), 99 );
        
		
		//synved_option_register('static_wordpress', $settings);

		if (!class_exists("DatabaseInterface")) {
			throw new Exception("Database interface didn't load");
		}
			
		
		$this->wp_query = new WP_Query($this->args);
		global $wpdb;
		$this->database = new DatabaseInterface($wpdb);
		
	}
	
	
    function admin_menu()
    {
	   $page_hook = add_menu_page( $this->plugin_name, $this->plugin_name, 'update_core', 'static_wordpress', array(&$this, 'settings_page') );
	   #add_submenu_page( 'wpsf', __( 'Settings', 'wp-settings-framework' ), __( 'Settings', 'wp-settings-framework' ), 'update_core', 'wpsf', array(&$this, 'settings_page') );
	   #add_options_page( $this->plugin_name, $this->plugin_name, 'manage_options', 'static_wordpress', array(&$this, 'settings_page'));
	   
    }
	
	function settings_page()
	    {
		   ?>
		   <div class="wrap">
			  <div id="icon-options-general" class="icon32"></div>
			  <h2>WP Settings Framework Example</h2>
			  <?php
			  // Output your settings form
			  $this->wpsf->settings();
			  ?>
		   </div>
		   <?php
	    }
	

	function activate() {
		global $wpdp;		
		$db = new DatabaseInterface($wpdb);		
		$db->create_database_tables();
	}
	
	public function scan_entire_site(){
		$wpi = new Wordpress_Interface();
	}


	function static_wordpress_required_plugins() {

    /**
     * Array of plugin arrays. Required keys are name and slug.
     * If the source is NOT from the .org repo, then source is also required.
     */
    $plugins = array(

        // This is an example of how to include a plugin pre-packaged with a theme.
        array(
            'name'               => 'Redux Framework', // The plugin name.
            'slug'               => 'redux-framework', // The plugin slug (typically the folder name).
            'required'           => true, // If false, the plugin is only 'recommended' instead of required.
            'force_activation'   => true, // If true, plugin is activated upon theme activation and cannot be deactivated until theme switch.
        )
    );

    /**
     * Array of configuration settings. Amend each line as needed.
     * If you want the default strings to be available under your own theme domain,
     * leave the strings uncommented.
     * Some of the strings are added into a sprintf, so see the comments at the
     * end of each line for what each argument will be.
     */
    $config = array(
        'id'           => 'static_wordpress',                 // Unique ID for hashing notices for multiple instances of TGMPA.
        'default_path' => '',                      // Default absolute path to pre-packaged plugins.
        'menu'         => 'static-wordpress-install-plugins', // Menu slug.
        'has_notices'  => true,                    // Show admin notices or not.
        'dismissable'  => true,                    // If false, a user cannot dismiss the nag message.
        'dismiss_msg'  => '',                      // If 'dismissable' is false, this message will be output at top of nag.
        'is_automatic' => false,                   // Automatically activate plugins after installation or not.
        'message'      => '',                      // Message to output right before the plugins table.
        'strings'      => array(
            'page_title'                      => __( 'Install Required Plugins', 'tgmpa' ),
            'menu_title'                      => __( 'Install Plugins', 'tgmpa' ),
            'installing'                      => __( 'Installing Plugin: %s', 'tgmpa' ), // %s = plugin name.
            'oops'                            => __( 'Something went wrong with the plugin API.', 'tgmpa' ),
            'notice_can_install_required'     => _n_noop( 'This theme requires the following plugin: %1$s.', 'This theme requires the following plugins: %1$s.', 'tgmpa' ), // %1$s = plugin name(s).
            'notice_can_install_recommended'  => _n_noop( 'This theme recommends the following plugin: %1$s.', 'This theme recommends the following plugins: %1$s.', 'tgmpa' ), // %1$s = plugin name(s).
            'notice_cannot_install'           => _n_noop( 'Sorry, but you do not have the correct permissions to install the %s plugin. Contact the administrator of this site for help on getting the plugin installed.', 'Sorry, but you do not have the correct permissions to install the %s plugins. Contact the administrator of this site for help on getting the plugins installed.', 'tgmpa' ), // %1$s = plugin name(s).
            'notice_can_activate_required'    => _n_noop( 'The following required plugin is currently inactive: %1$s.', 'The following required plugins are currently inactive: %1$s.', 'tgmpa' ), // %1$s = plugin name(s).
            'notice_can_activate_recommended' => _n_noop( 'The following recommended plugin is currently inactive: %1$s.', 'The following recommended plugins are currently inactive: %1$s.', 'tgmpa' ), // %1$s = plugin name(s).
            'notice_cannot_activate'          => _n_noop( 'Sorry, but you do not have the correct permissions to activate the %s plugin. Contact the administrator of this site for help on getting the plugin activated.', 'Sorry, but you do not have the correct permissions to activate the %s plugins. Contact the administrator of this site for help on getting the plugins activated.', 'tgmpa' ), // %1$s = plugin name(s).
            'notice_ask_to_update'            => _n_noop( 'The following plugin needs to be updated to its latest version to ensure maximum compatibility with this theme: %1$s.', 'The following plugins need to be updated to their latest version to ensure maximum compatibility with this theme: %1$s.', 'tgmpa' ), // %1$s = plugin name(s).
            'notice_cannot_update'            => _n_noop( 'Sorry, but you do not have the correct permissions to update the %s plugin. Contact the administrator of this site for help on getting the plugin updated.', 'Sorry, but you do not have the correct permissions to update the %s plugins. Contact the administrator of this site for help on getting the plugins updated.', 'tgmpa' ), // %1$s = plugin name(s).
            'install_link'                    => _n_noop( 'Begin installing plugin', 'Begin installing plugins', 'tgmpa' ),
            'activate_link'                   => _n_noop( 'Begin activating plugin', 'Begin activating plugins', 'tgmpa' ),
            'return'                          => __( 'Return to Required Plugins Installer', 'tgmpa' ),
            'plugin_activated'                => __( 'Plugin activated successfully.', 'tgmpa' ),
            'complete'                        => __( 'All plugins installed and activated successfully. %s', 'tgmpa' ), // %s = dashboard link.
            'nag_type'                        => 'updated' // Determines admin notice type - can only be 'updated', 'update-nag' or 'error'.
        )
    );

    tgmpa( $plugins, $config );

}



}

$wpStaticWordpress = new StaticWordpress();
