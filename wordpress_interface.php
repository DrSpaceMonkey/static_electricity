<?php

class Wordpress_Interface {

	function get_index_uris($retval = array()) {
		$total_pages = $this->total_pages();
		for ($i = 1; $i <= $total_pages; $i++)	{
			array_push($retval, get_pagenum_link($i));
		}
		return $retval;
	}
	
	private function total_pages() {
		$max_page = $this->wp_query->max_num_pages;
		return $max_page;
	}
	
	private function get_number_of_posts_per_page(){
		return get_option('posts_per_page');
	}



	public function get_tag_uris($retval = array()) {
		$tags = get_tags(array(	hide_empty => false));
		foreach ( $tags as $tag ) {
			array_push($retval, get_tag_link( $tag->term_id ));
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

	public function get_all_uris() {
		$retval = $this->get_index_uris();
		$retval = $this->get_tag_uris($retval);
		$retval = $this->get_page_uris($retval);
		$retval = $this->get_post_uris($retval);
		return $retval;		
	}
	
	
}