<?php

class DatabaseInterface {
	private $wpdb = NULL;
	private $page_checksum_table_name = NULL;
	function __construct(){
		global $wpdb;
			
		$this->wpdb = $wpdb;
		$prefix = $wpdb->prefix;
		if (strcasecmp($prefix, "") == 0)
			throw new Exception ("Something went wrong with Wordpress:");
		
		$this->page_checksum_table_name = $prefix . "static_wordpress_page_checksums"; 
	}
	
	public function create_database_tables(){
	
		echo "Creating database";
		var_dump($this);
		$charset_collate = '';

		if ( ! empty( $this->wpdb->charset ) ) {
		  $charset_collate = "DEFAULT CHARACTER SET {$this->wpdb->charset}";
		}

		if ( ! empty( $this->wpdb->collate ) ) {
		  $charset_collate .= " COLLATE {$this->wpdb->collate}";
		}
		
		$sql = "CREATE TABLE $this->page_checksum_table_name (
		  id mediumint(9) NOT NULL AUTO_INCREMENT,
		  uri varchar(255) DEFAULT '' NOT NULL,
		  checksum varchar(55) DEFAULT '' NOT NULL,
		  UNIQUE KEY id (id),
		  UNIQUE KEY url (uri)
		) $charset_collate;";

		echo $sql;

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );


	}
}
