<?php

class DatabaseInterface {
	private $db_version_key = "static_electricity_db_version";
	private $db_version = "1.0";
	private $wpdb = NULL;
	private $page_checksum_table_name = NULL;
	
	
	function __construct(){						
		global $wpdb;
		$this->wpdb = $wpdb;
		$prefix = $wpdb->prefix;
		if (strcasecmp($prefix, "") == 0)
			throw new Exception ("Something went wrong with Wordpress:");
		
		$this->page_checksum_table_name = $prefix . "static_electricity_page_checksums"; 
	}
	
	public function save_uri_checksum($uri, $checksum) {	
		global $wpdb;
			if ($this->uri_is_stale($uri, $checksum)) {
				$wpdb->replace(
					$this->page_checksum_table_name, 
					array( 
						'uri' => $uri,
						'checksum' => $checksum
					)
				);
			}
	
	}
	
	public function clear_checksums() {
		$this->truncate_table($this->page_checksum_table_name);
	}
	
	private function truncate_table($table_name) {
		global $wpdb;
		$delete = $wpdb->query("TRUNCATE TABLE `$table_name`");
	}
	
	public function uri_is_stale($uri, $checksum) {					
		global $wpdb;
		$stored_checksum = $wpdb->get_var( $wpdb->prepare( 
		"
			SELECT checksum 
			FROM $this->page_checksum_table_name 
			WHERE uri = %s 
		", $uri ));
		
		return ($checksum !== $stored_checksum);
	}
	
	
	public function create_database_tables(){
	
		$charset_collate = '';

		if ( ! empty( $this->wpdb->charset ) ) {
		  $charset_collate = "DEFAULT CHARACTER SET {$this->wpdb->charset}";
		}

		if ( ! empty( $this->wpdb->collate ) ) {
		  $charset_collate .= " COLLATE {$this->wpdb->collate}";
		}
		
		$id_index_name = $this->page_checksum_table_name . "_id";
		$uri_index_name = $this->page_checksum_table_name . "_uri";
		
		$sql = "CREATE TABLE $this->page_checksum_table_name (
		  id mediumint(9) NOT NULL AUTO_INCREMENT,
		  uri varchar(255) DEFAULT '' NOT NULL,
		  checksum varchar(55) DEFAULT '' NOT NULL,
		  UNIQUE KEY $id_index_name (id),
		  UNIQUE KEY $uri_index_name (uri)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		add_option( $this->db_version_key, $this->db_version );
		dbDelta( $sql );


	}
}
