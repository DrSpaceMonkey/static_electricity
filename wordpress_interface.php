<?php

class Wordpress_Interface {

	private $wp_query = NULL;

	function __construct(){
		$this->wp_query = new WP_Query(array( 
			'post_type'=>'post',
			'post_status'=>'publish'
		));
	}


	function get_index_uris($retval = array()) {
		
		for ($i = 1; $i <= $this->total_pages(); $i++)	{
			$u = get_pagenum_link($i);
			$u  = str_replace('rescan_blog_action=1', '', $u);
			$u = rtrim($u, "?");
			array_push($retval, $u );
		}
		return $retval;
	}
	
	private function total_pages() {
		return $this->wp_query->max_num_pages;
	}
	
	private function get_number_of_posts_per_page(){
		return get_option('posts_per_page');
	}
	
	private function get_attachment_ids(){
		#return  get_posts('post_type=attachment');
		$args = array(
		    'post_type' => 'attachment',
		    'numberposts' => -1,
		    'post_status' => null,
		    'post_parent' => null, // any parent
		    ); 
		$retval = array();
		$attachments = get_posts($args);
		foreach ($attachments as $post) {
			array_push($retval, $post->ID);
		}
		
		return $retval;
	}
	
	public function get_attachment_uris($retval = array()) {
		$attachments = $this->get_attachment_ids();
		foreach($attachments as $attachment) {
			array_push($retval, wp_get_attachment_url( $attachment ));
		}
		return $retval;
	}



	public function get_tag_uris($retval = array()) {
	
		$tags = get_tags(array(	hide_empty => false));
		foreach ( $tags as $tag ) {
			array_push($retval, get_tag_link( intval($tag->term_id) ));
		}
		return $retval;
	}

	public function get_page_uris($retval = array()) {
                $pages = get_all_page_ids();
                foreach ( $pages as $page ) {			
                        array_push($retval, get_page_link( $page ));
                }
                return $retval;
        }

	public function get_post_uris($retval = array()) {
		global $wpdb;
		$sql="
		SELECT id 
		FROM ". $wpdb->prefix . "posts 
		WHERE post_status='publish' 
			AND (
				post_type='post' 
				OR post_type='revision'
				)";
		$posts = $wpdb->get_results($sql);

		foreach ($posts as $post) {
			array_push($retval, get_permalink($post->id));
		}
		return $retval;
	}

	
	
}