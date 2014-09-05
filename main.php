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
	protected $rescan_entire_blog = false;
	protected $clear_checksums = false;
	protected $rescan_blog = false;
	protected $rescan_blog_action_trigger = null;
	
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
		require_once dirname(__FILE__) . '/class-tgm-plugin-activation.php';
		
		register_activation_hook( __FILE__, array( 'StaticWordpress', 'activate' ) );		
		
		require_once dirname(__FILE__) . '/admin/admin-init.php';
		require_once dirname(__FILE__) . '/http_build_url.php';
		require_once dirname(__FILE__) . '/web_interface.php';
		require_once dirname(__FILE__) . '/wordpress_interface.php';
          require_once dirname(__FILE__) . '/database_interface.php';
          require_once dirname(__FILE__) . '/FileInterface/base_file_interface.php';
		$this->retrieve_settings();
	}

	
	function retrieve_settings() {
		global $static_electricity_settings;
		$this->clear_checksums = $static_electricity_settings['clear_checksum'];
		$this->rescan_blog_action_trigger = isset($_GET["rescan_blog_action"]) ? $_GET["rescan_blog_action"] : false;
		$this->rescan_blog = $static_electricity_settings['rescan_blog'];
	}
	
	function echo_flush($message){
	
		global $running_wp_cli;
		
		if ($running_wp_cli) {
			WP_CLI::success($message);
		} else {		
			echo '<p>' . $message . '</p>';
			flush();
			ob_flush();
		}
		
	}
	
	function write_to_message_log(){
		
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
			array(
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
			),
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
	
	function save_404(){	
		global $static_electricity_settings;		
		$filename = $static_electricity_settings['404_page_file_location'];
		$u = get_home_url() . "/404.this.should.never.work";		
		$web_interface = new WebInterface($u);
		$this->save_to_working_directory($web_interface->replace_uris_in_content(), $filename);
	}
	
	
	function process_uris($uris, $uris_to_skip) {
		ini_set('memory_limit', '-1');
		$processed_uris = array();
		$harvested_uris = array();
		
		$this->save_404();
		
		 
		foreach($uris as $u => &$values) {		
			//If $values is empty, then $u needs to be fetched and possibly processed
			if (empty($values) && (array_search(parse_url($u)['path'], $uris_to_skip) === FALSE)) {
				$processed_uri = $this->process_uri($u);
				if ($processed_uri !== false) {
					$uris_to_skip[] = parse_url($u)['path'];
					foreach($processed_uri['harvested_uris'] as $h_u) {
						if ((!isset($uris[$h_u])) && (array_search($h_u, $harvested_uris) === FALSE))
							$harvested_uris[] = $h_u;
					}
					$harvested_uris = array_unique($harvested_uris);
					$uris[$u] = $processed_uri['values'];
				}
			}
		}
		
		$this->echo_flush("Found " . count($harvested_uris) . " URIs in harvested HTML pages. Fetching...");
		if (count($harvested_uris) > 0)
		{
			return array_merge($uris, $this->process_uris(array_fill_keys(array_unique($harvested_uris), 0), $uris_to_skip));
		} else {
			return $uris;
		}
		
	}
	
	function process_uri($u) {		
		global $static_electricity_settings;
		$dbi = new DatabaseInterface();
		$filename = $this->get_file_name_from_uri($u);
		$directory = $this->get_directory_name_from_uri($u);
		$path = $directory . $filename;
		$retval = array();
		$bytes_written = 0;
		$retval['harvested_uris'] = array();
		
		if (!WebInterface::is_a_local_uri($u))
			return false;
		
		
		try {					
			$this->echo_flush("Fetching $u ...");				
			$web_interface = new WebInterface($u);
			$md5_result = md5($web_interface->get_content());
			$stale = $dbi->uri_is_stale($u, $md5_result);
			if ($stale or $this->clear_checksums) {				
				$dbi->save_uri_checksum($u, $md5_result);
				$this->echo_flush("Processing $path ...");
				
				$is_html = $web_interface->is_html();
				$is_css = pathinfo($u, PATHINFO_EXTENSION) == "css";
				
				if (!$web_interface->is_404()) {
				
					if ($is_html or $is_css){
					
						$replacement_domain = $static_electricity_settings['replacement_uri_prefix'];
						$html_content = $web_interface->get_HTML_content();
						$fixed_content_to_save = $web_interface->replace_uris_in_content($html_content, $replacement_domain);
					
						$bytes_written = $this->save_to_working_directory($fixed_content_to_save, $path);
						
						$content_to_harvest = $web_interface->replace_uris_in_content($html_content, get_home_url());
						
						$retval['harvested_uris'] = $web_interface->get_local_linked_resources($content_to_harvest, get_home_url(), $u);
					} else {
						$bytes_written = $this->save_to_working_directory($web_interface->get_content(), $path);
						
					}
				} else {
					WP_CLI::warning("$u returned 404");
				}				
			} else {
				$this->echo_flush("File is up to date ($path)");	
				$bytes_written = strlen($web_interface->get_content());
			}
			
			$retval['values'] = array(
			"checksum" => $md5_result,
			"working_file" =>  $this->get_working_path($path),
			"is_404" => $web_interface->is_404(),
			"stale" => $stale,
			"size" => $bytes_written);		
			
				
			
				
		} catch (Exception $e){
			$web_interface = NULL;
			return false;
		}
		$web_interface = NULL;
		return $retval;
	}
	
	function get_working_path($path = "") {
		global $static_electricity_settings;		
		$filename = ltrim($path, '/');	
		$working_dir = trailingslashit($static_electricity_settings['static_electricity_working_directory']);
		return $working_dir . $filename;
		
	}
	
	//strlen
	function get_file_name_from_uri($path) {
		global $static_electricity_settings;		
		$uri_parts = parse_url($path);
		$basename = basename($uri_parts['path']);
		if (strpos($basename,'.') !== false) {
			$ext = pathinfo($filename, PATHINFO_EXTENSION);
			if (strcasecmp($ext, "php") == 0)
				$basename = str_ireplace($ext, "html", $basename);
			return $basename;
		} else {
			return $static_electricity_settings['index_page_filename'];
		}
	}
	
	function get_directory_name_from_uri($path) {	
		$uri_parts = parse_url($path);
		$basename = basename($uri_parts['path']);
		if (strpos($basename,'.') !== false) {			
			return trailingslashit(dirname($uri_parts['path']));
		} else {
			return trailingslashit($uri_parts['path']);
		}
	}
	
	function save_to_working_directory($content, $filename) {
	
		try {
			if (file_exists($filename) or strlen($content) == 0)
				return false;
				
			$output_file = $this->get_working_path($filename);
			$output_dir = dirname($output_file);
			if (!(file_exists($output_dir) && is_dir($output_dir)))
				mkdir($output_dir, 0755, true);
			
			$bytes_written = 0;		
			$file = fopen($output_file, 'w');
			$bytes_written = $this->fwrite_stream($file, $content);	
			fclose($file);
			chmod($file, 0444);
			$file = NULL;
			if ($bytes_written === false)
				return false;			
			return $bytes_written;
		} catch (Exception $e) {		
			WP_CLI::error($e);
			return false;
		}
		
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
		
		$this->get_upload_files();
		$this->get_theme_files();
		
		
		$this->echo_flush('<pre id="#inner_html_sideload_object">');
	
		$this->echo_flush('Scanning blog');
		
		$uris = $this->scan_pages();
		
		$this->echo_flush('Removing any duplicate URIs');		
		$uris = array_unique($uris);						
		$this->echo_flush('Found ' . count($uris) . ' URIs');
		$results = ($this->process_uris(array_fill_keys($uris, 0), array()));
		
		$this->echo_flush('Processed a total of ' . count($results) . ' URIs');
		$this->echo_flush('Moving files to final destination...');
		
		$this->relocate_working_files();
		
		
		echo '</pre>';
		
	}
	
	function get_theme_files() {

		$theme_root = get_theme_root();
		
		$this->get_directory_files($theme_root);

	}
	
	function get_upload_files() {
		$upload_root = wp_upload_dir()['basedir'];
		
		$this->get_directory_files($upload_root);

	}
	
	function get_directory_files($dir_to_scan) {
		global $static_electricity_settings;		
		$working_dir = trailingslashit($static_electricity_settings['static_electricity_working_directory']);
		
		
		$base_directory = get_home_path();
		
		$file_list = FileInterface\BaseFileInterface::get_file_list($dir_to_scan);
		WP_CLI::success("Scanning $dir_to_scan");
		foreach($file_list as $file) {			
				
			$ext = pathinfo($file, PATHINFO_EXTENSION);
			if ($ext != "php") {
				$new_file_location = $working_dir. str_replace($base_directory, "", $file);
				
				if (!is_dir(dirname($new_file_location))) {
					WP_CLI::success(dirname($new_file_location));
					mkdir(dirname($new_file_location), 0755, true);
				}
				if (copy($file, $new_file_location)) {
					WP_CLI::success("Copied file $file to $new_file_location");
				} else {
					WP_CLI::warning("Copying $file to $new_file_location failed!");
					$errors= error_get_last();
					var_dump($errors);
				}
			}
		}
	}
	
	function relocate_working_files() {
		global $static_electricity_settings;		
		$working_dir = trailingslashit($static_electricity_settings['static_electricity_working_directory']);
		
		$file = new FileInterface\FileInterface();
		$file->the_chosen_one()->clone_directory_to_destination($working_dir);
		
	}
	
	function footer_inject(){
		global $static_electricity_settings;
		global $reduxConfig;
		
		if ($this->rescan_blog == 1) {
		
			$reduxConfig->ReduxFramework->set('rescan_blog', false);
			?> 
			<script>
				window.open('<?php echo get_home_url() ?>/?rescan_blog_action=1', '_blank');
			</script>
			
			<?php
			
		
			//wp_register_script( 'static-electricity-sideload', plugins_url( 'sideload.js' , __FILE__ ), array('jquery'));
			
			//wp_localize_script( 'static-electricity-sideload', 'staticElectricity', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )));
			
			//wp_enqueue_script( 'static-electricity-sideload');
			
			//add_action("wp_ajax_nopriv_rescan_entire_blog", "rescan_entire_blog");
			#add_action("wp_ajax_rescan_entire_blog", "rescan_entire_blog");
		}
	}
	
    function admin_menu()
    {	   
		global $static_electricity_settings;
		global $reduxConfig;
		
		
		if (isset($static_electricity_settings)) {
			if ($this->clear_checksums) {
				$db = new DatabaseInterface($wpdb);
				
				
				FileInterface\FileInterface::deleteDir($this->get_working_path());
				$db->clear_checksums();
				$reduxConfig->ReduxFramework->set('clear_checksum', false);
			}
			
			$reduxConfig->ReduxFramework->set('rescan_blog', false);
			if ($this->rescan_blog_action_trigger == 1) {		
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

        array(
            'name'      => 'Amazon Web Services',
            'slug'      => 'amazon-web-services',
            'required'  => true,
            'force_activation'  => true,
        ),
        array(
            'name'      => 'Redux Framework',
            'slug'      => 'redux-framework',
            'required'  => true,
            'force_activation'  => true,
        ),
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
//wp_register_script( "static_electricity_javascript", WP_PLUGIN_URL.'/static_electricity/sideload.js', array('jquery') );
//add_action('plugins_loaded', array($wpStaticWordpress, 'admin_init'));  

add_action('admin_menu', array($wpStaticWordpress, 'admin_menu'));
add_action('tgmpa_register', array($wpStaticWordpress, 'static_electricity_required_plugins'));

//Version parameters need to be removed from script URLs
function vc_remove_wp_ver_css_js( $src ) {
    if ( strpos( $src, 'ver=' ) )
        $src = remove_query_arg( 'ver', $src );
    return $src;
}
add_filter( 'style_loader_src', 'vc_remove_wp_ver_css_js', 9999 );
add_filter( 'script_loader_src', 'vc_remove_wp_ver_css_js', 9999 );
