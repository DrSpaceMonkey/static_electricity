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


	public function __construct() {
		register_activation_hook( __FILE__, array( 'StaticWordpress', 'activate' ) );
		include_once dirname(__FILE__) . '/web_interface.php';
                include_once dirname(__FILE__) . '/database_interface.php';

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
		#$sW = new StaticWordpress();		
		
		echo "Come on, fucking wordpress sucks";
		$db->create_database_tables();
	}

	function total_pages() {
		$max_page = $this->wp_query->max_num_pages;
		return $max_page;
	}

	function get_index_uris($retval = array()) {
		$total_pages = $this->total_pages();
		for ($i = 1; $i <= $total_pages; $i++)	{
			array_push($retval, get_pagenum_link($i));
		}
		return $retval;
	}

	function get_tag_uris($retval = array()) {
		$tags = get_tags(array(	hide_empty => false));
		foreach ( $tags as $tag ) {
			array_push($retval, get_tag_link( $tag->term_id ));
		}
		return $retval;
	}

	function get_page_uris($retval = array()) {
                $pages = get_all_page_ids();
                foreach ( $pages as $page ) {			
                        array_push($retval, get_page_link( $page ));
                }
                return $retval;
        }

	function get_post_uris($retval = array()) {
		global $wpdb;
		$sql="SELECT id FROM ". $wpdb->prefix . "posts WHERE post_status='publish' AND (post_type='post' OR post_type='revision')";
		$posts = $wpdb->get_results($sql);

		foreach ($posts as $post) {
			array_push($retval, get_permalink($post->id));
		}
		return $retval;
	}

	function get_all_uris() {
		$retval = $this->get_index_uris();
		
	}



}

$wpStaticWordpress = new StaticWordpress();
