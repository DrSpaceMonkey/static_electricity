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
	private $option_group = 'static_wordpress_option_group';
	private $slug = "static_wordpress";
	private $rescan_entire_blog = false;
	
	
	private $settings = array(
		'replace_uris' => NULL,
		'new_uri_prefix' => NULL,
	);
	
	private $DOM_tags_to_scan = array (
		'ahref' => NULL,
		'img' => NULL,
		'css' => NULL,
		'javascript' => NULL,
	);
	
	private $wp_objects_to_scan = array (
		'index' => NULL,
		'tags' => NULL,
		'pages' => NULL,
		'posts' => NULL,
		'attachments' => NULL,
	);
	
	
	
	public function __construct() {		
		global $wpdb;
		
		
		
		register_activation_hook( __FILE__, array( 'StaticWordpress', 'activate' ) );
		require_once  dirname(__FILE__) . '/web_interface.php';
		#require_once  dirname(__FILE__) . '/class-tgm-plugin-activation.php';
          require_once  dirname(__FILE__) . '/database_interface.php';
		#require_once dirname(__FILE__) . '/wp-settings-framework.php';
		require_once dirname(__FILE__) . '/curl.php';
		require_once dirname(__FILE__). '/admin/admin-init.php';
		
		
		global $redux_demostatic_wordpress_option_group;
		
		var_dump($redux_demostatic_wordpress_option_group);
		
		#add_action( 'tgmpa_register', array( &$this, 'static_wordpress_required_plugins' ));
		
          #$this->wpsf = new WordPressSettingsFramework( dirname(__FILE__) . '/settings/settings.php', $this->option_group );
		
		#$this->retrieve_settings();
		
		//
		
		add_action( 'admin_menu', array(&$this, 'admin_menu'), 99 );
        
		
		
		if (!class_exists("DatabaseInterface")) {
			throw new Exception("Database interface didn't load");
		}
			
		
		$this->wp_query = new WP_Query($this->args);
		$this->database = new DatabaseInterface($wpdb);
			
		
	}

	
	function retrieve_settings() {
		$this->DOM_tags_to_scan['ahref'] = wpsf_get_setting( $this->option_group, 'basic_settings', 'ahref' );
		$this->DOM_tags_to_scan['img'] = wpsf_get_setting( $this->option_group, 'basic_settings', 'img' );
		$this->DOM_tags_to_scan['css'] = wpsf_get_setting( $this->option_group, 'basic_settings', 'css' );
		$this->DOM_tags_to_scan['javascript'] = wpsf_get_setting( $this->option_group, 'basic_settings', 'javascript');
		
		$this->wp_objects_to_scan['index'] = wpsf_get_setting( $this->option_group, 'basic_settings', 'index' );
		$this->wp_objects_to_scan['tags'] = wpsf_get_setting( $this->option_group, 'basic_settings', 'tags' );
		$this->wp_objects_to_scan['pages'] = wpsf_get_setting( $this->option_group, 'basic_settings', 'pages' );
		$this->wp_objects_to_scan['posts'] = wpsf_get_setting( $this->option_group, 'basic_settings', 'posts' );
		$this->wp_objects_to_scan['attachments'] = wpsf_get_setting( $this->option_group, 'basic_settings', 'attachments' );
		
		$this->settings['replace_uris'] = wpsf_get_setting( $this->option_group, 'basic_settings', 'replace_uri_in_links' );
		$this->settings['new_uri_prefix'] = wpsf_get_setting( $this->option_group, 'basic_settings', 'replacement_uri_prefix' );

	}
	
	
	function rescan_entire_blog() {
		
		echo '<pre>';
	
		echo '<p>Scanning blog</p>';
		
		var_dump($this->settings);
		
		$retval = array();
		
		$wpi = new Wordpress_Interface();
		
		
		if ($this->wp_objects_to_scan['index'])			
			$retval = $wpi->get_index_uris($retval);
			
		if ($this->wp_objects_to_scan['tags'])
			$retval = $wpi->get_tag_uris($retval);
			
		if ($this->wp_objects_to_scan['pages'])
			$retval = $wpi->get_page_uris($retval);
			
		if ($this->wp_objects_to_scan['posts'])		
			$retval = $wpi->get_post_uris($retval);
			
		if ($this->wp_objects_to_scan['attachments'])	
			$retval = $wpi->get_attachment_uris($retval);

		$retval = array_unique($retval);
		
		echo 'Found ' . count($retval) . ' URIs';
		
		foreach($retval as $uri) {			
			$web_interface = new WebInterface($uri);
			echo $web_interface->get_mime_type();
		}
		
		echo '</pre>';
		
	}
	
    function admin_menu()
    {
	   $page_hook = add_menu_page( $this->plugin_name, $this->plugin_name, 'update_core', $this->slug, array(&$this, 'display_action') );
	   #add_submenu_page( 'wpsf', __( 'Settings', 'wp-settings-framework' ), __( 'Settings', 'wp-settings-framework' ), 'update_core', 'wpsf', array(&$this, 'settings_page') );
	   #add_options_page( $this->plugin_name, $this->plugin_name, 'manage_options', 'static_wordpress', array(&$this, 'settings_page'));
	   
    }
    
    
	function display_action() {
		if(isset($_GET["rescan_blog"]))
			$this->rescan_entire_blog = ($_GET["rescan_blog"] == true);

		if ($this->rescan_entire_blog) {		
			$this->rescan_entire_blog();
		} else {
			$this->settings_page();
		}
	}
	
	function settings_page()
	    {
		   ?>
		   <div class="wrap">
			  <div id="icon-options-general" class="icon32"></div>
			  <h2><?php echo $this->plugin_name;?> settings</h2>
			  <?php
			  // Output your settings form
			  $this->wpsf->settings();
			  ?>
		   </div>
		   <a class="button-primary" target="_blank" href="<?php echo $_SERVER['SCRIPT_NAME'] . "?page=" . $this->slug ; ?>&rescan_blog=1">Rescan blog</a>
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
        'dismissable'  => false,                    // If false, a user cannot dismiss the nag message.
        'dismiss_msg'  => 'There are unmet dependencies',                      // If 'dismissable' is false, this message will be output at top of nag.
        'is_automatic' => true,                   // Automatically activate plugins after installation or not.
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
