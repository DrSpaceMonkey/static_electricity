<?php
/*
Plugin Name: Static Electricity
Plugin URI: https://github.com/DrSpaceMonkey/static_electricity/
Description: Generates a static HTML version of your Wordpress site
Author: Steven Allen
Version: 1.0
Author URI: https://github.com/DrSpaceMonkey/
*/


class StaticWordpress {

	protected $database = NULL;
	private $wpsf;
	private $plugin_name = 'Static Electricity';
	private $slug = "static_electricity";
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
		require_once dirname(__FILE__) . '/admin/admin-init.php';
          require_once dirname(__FILE__) . '/FileInterface/base_file_interface.php';
		require_once dirname(__FILE__) . '/web_interface.php';
		require_once dirname(__FILE__) . '/wordpress_interface.php';
          require_once dirname(__FILE__) . '/database_interface.php';
	}

	
	function retrieve_settings() {
	/*
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
		$this->settings['new_uri_prefix'] = wpsf_get_setting( $this->option_group, 'basic_settings', 'replacement_uri_prefix' );*/

	}
	
	function echo_flush($message){
		echo '<p>' . $message . '</p>';
		flush();
		ob_flush();
	}
	
	function scan_pages() {		
		$retval = array();
		global $static_electricity_settings;
		global $reduxConfig;	
		
		$scan_args = array (
			array(
				'option_name' => 'index',
				'function' => 'get_index_uris',
				'type' => 'index'
			),
			/*array(
				'option_name' => 'tags',
				'function' => 'get_tag_uris',
				'type' => 'tag'
			),
			array(
				'option_name' => 'pages',
				'function' => 'get_page_uris',
				'type' => 'page'
			),
			array(
				'option_name' => 'posts',
				'function' => 'get_post_uris',
				'type' => 'post'
			),
			array(
				'option_name' => 'attachments',
				'function' => 'get_attachment_uris',
				'type' => 'attachment'
			),*/
		);
		foreach($scan_args as $arg) {		
			$uris = $this->scan_page_type($arg['option_name'], $arg['function'], array());
			$retval = array_merge($retval, $uris);
			$type = $arg['type'];
			$count = count($uris);
		}
		return $retval;
	}
	
	function scan_page_type($option_name, $function, $uris) {
		global $static_electricity_settings;
		$wpi = new Wordpress_Interface();
		if ($static_electricity_settings['harvest_options'][$option_name] == '1')	 {	
			$uris = call_user_func(array($wpi, $function), $uris);
		} else {
		}
		return $uris;
	}
	
	
	function process_uris($uris) {
		$processed_uris = array();
		$harvested_uris = array();
		foreach($uris as $u => &$values) {		
			//If $values is empty, then $u needs to be fetched and possibly processed
			if (empty($values)) {
				$processed_uri = $this->process_uri($u);
				$harvested_uris = array_unique(array_merge($harvested_uris, $processed_uri['harvested_uris']));
				var_dump($harvested_uris);
				$values = $processed_uri['values'];
			}
		}
		return array_unique(array_merge($uris, $this->process_uris(array_fill_keys($harvested_uris, 0))));
		
	}
	
	function process_uri($u) {
		$dbi = new DatabaseInterface();
		$retval = array();
		$retval['harvested_uris'] = array();
		
		try {					
			$this->echo_flush("Fetching $u ...");				
			$web_interface = new WebInterface($u);
			$md5_result = md5($web_interface->get_content());
			$stale = $dbi->uri_is_stale($u, $md5_result);
			if ($stale) {
				$filename = $this->get_file_name_from_uri($u);
				$directory = $this->get_directory_name_from_uri($u);
				if ($web_interface->is_html() && !$web_interface->is_404() ){
					$written = $this->save_to_working_directory($web_interface->get_HTML_content(), $directory . $filename);
					
					$retval['harvested_uris'] = $web_interface->get_local_linked_resources();
				} else {					
					$written = $this->save_to_working_directory($web_interface->get_content(), $directory . $filename);
				}
				$this->echo_flush($written);
				$dbi->save_uri_checksum($u, $md5_result);
			} else {
				
			}
			
			$retval['values'] = array(
				"checksum" => $md5_result,
				"working_file" => $written,
				"is_404" => $web_interface->is_404(),
				"stale" => $stale);
				
		} catch (Exception $e){
			return false;
		}
		return $retval;
	}
	
	
	//strlen
	function get_file_name_from_uri($path) {
		global $static_electricity_settings;		
		$uri_parts = parse_url($path);
		$basename = basename($uri_parts['path']);
		if (strpos($basename,'.') !== false) {
			return $basename;
		} else {
			return $static_electricity_settings['index_page_filename'];
		}
	}
	
	function get_directory_name_from_uri($path) {	
		$uri_parts = parse_url($path);
		$basename = basename($uri_parts['path']);
		if (strpos($basename,'.') !== false) {			
			return trailingslashit(dirname($basename));
		} else {
			return trailingslashit($uri_parts['path']);
		}
	}
	
	function save_to_working_directory($content, $filename) {	
		
		global $static_electricity_settings;		
		$filename = ltrim($filename, '/');	
		$working_dir = trailingslashit($static_electricity_settings['static_electricity_working_directory']);
		$output_file = $working_dir . $filename;
		$output_dir = dirname($output_file);
		if (!(file_exists($output_dir) && is_dir($output_dir)))
			mkdir($output_dir, 0755, true);
		
		$bytes_written = 0;		
		$file = fopen($output_file, 'w');
		$bytes_written = $this->fwrite_stream($file, $content);		
		if ($bytes_written === false)
			return false;			
		fclose($file);
		return $output_file;
		
	}
	
	function fwrite_stream($fp, $string) {
	    for ($written = 0; $written < strlen($string); $written += $fwrite) {
		   $fwrite = fwrite($fp, substr($string, $written));
		   if ($fwrite === false) {
			  return $written;
		   }
	    }
	    return $written;
	}
	
	
	
	function rescan_entire_blog() {
		
		global $static_electricity_settings;
		global $reduxConfig;
		
		echo '<pre>';
	
		echo '<p>Scanning blog</p>';
		// $static_electricity_settings['rescan_blog']
		$uris = $this->scan_pages();		
		$this->echo_flush('Removing any duplicate URIs');		
		$uris = array_unique($uris);						
		$this->echo_flush('Found ' . count($uris) . ' URIs');
		var_dump($this->process_uris(array_fill_keys($uris, 0)));
		
		echo '</pre>';
		exit;
		
	}
	
    function admin_menu()
    {	   
		global $static_electricity_settings;
		global $reduxConfig;
				
		/*if (is_null($static_electricity_settings)){
			throw new Exception("Settings didn't load");
		}*/
		
		if ($static_electricity_settings['clear_checksum']) {
			$db = new DatabaseInterface($wpdb);
			$db->clear_checksums();
			$reduxConfig->ReduxFramework->set('clear_checksum', false);
		}
			
		
		if(isset($_GET["rescan_blog_action"])) {
			$rescan_blog_action = ($_GET["rescan_blog_action"] == 1);
			$reduxConfig->ReduxFramework->set('rescan_blog', false);
			$reduxConfig->ReduxFramework->set('rescan_blog_action', true);
			if ($rescan_blog_action) {		
				$this->rescan_entire_blog();
			}
		}
}
    

	function activate() {
		global $wpdp;		
		$db = new DatabaseInterface($wpdb);		
		$db->create_database_tables();
	}
	
	public function scan_entire_site(){
		$wpi = new Wordpress_Interface();
	}


	function static_electricity_required_plugins() {

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
        'id'           => 'static_electricity',                 // Unique ID for hashing notices for multiple instances of TGMPA.
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
#add_action('plugins_loaded', array($wpStaticWordpress, 'admin_init'));  
add_action('wp_loaded', array($wpStaticWordpress, 'admin_menu'));
