<?php
/*
Plugin Name: Static Wordpress
Plugin URI: http://github.com
Description: Generates a static HTML version of your Wordpress site
Author: Steven Allen
Version: 1.0
Author URI: http://github.com
*/


class StaticWordpress {

	private $wp_query = NULL;
	protected $database = NULL;
	
	private $settings = array( 
		
	);


	public function __construct() {
		register_activation_hook( __FILE__, array( 'StaticWordpress', 'activate' ) );
		include_once dirname(__FILE__) . '/web_interface.php';
          include_once dirname(__FILE__) . '/database_interface.php';
          include_once dirname(__FILE__) . '/synved-options/synved-options.php';
		
		synved_option_register('static_wordpress', $settings);

		if (!class_exists("DatabaseInterface")) {
			throw new Exception("Database interface didn't load");
		}
			
		
		$this->wp_query = new WP_Query($this->args);
		global $wpdb;
		$this->database = new DatabaseInterface($wpdb);
		
	}



	function activate() {
		global $wpdp;		
		$db = new DatabaseInterface($wpdb);		
		$db->create_database_tables();
	}
	
	public function scan_entire_site(){
		$wpi = new Wordpress_Interface();
	}





}

$wpStaticWordpress = new StaticWordpress();
