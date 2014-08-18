<?php
/**
 * WordPress Settings Framework
 *
 * @author Gilbert Pellegrom
 * @link https://github.com/gilbitron/WordPress-Settings-Framework
 * @license MIT
 */

/**
 * Define your settings
 */
add_filter( 'wpsf_register_settings', 'staic_wordpress_settings' );
function staic_wordpress_settings( $wpsf_settings ) {
	
	$home_url = get_home_url();

    // More Settings section
    
   
    
    $wpsf_settings[] = array(
        'section_id' => 'basic_settings',
        'section_title' => 'Basic settings',
        'section_order' => 1,
        'fields' => array(	   
            array(
                'id' => 'replace_uri_in_links',
                'title' => 'Replace URIs',
                'desc' => "If checked, the hostname ($home_url) in links will be replaced with <em>URI prefix</em> (next setting).",
                'type' => 'checkbox',
                'std' => 1
            ),
            array(
                'id' => 'replacement_uri_prefix',
                'title' => 'URI prefix',
                'placeholder' => '/',
                'desc' => "The hostname ($home_url) will be replaced with this string in links if the checkbox above is checked.",
                'type' => 'text',
                'std' => '/'
            ),
            array(
                'id' => 'scanning_options',
                'title' => 'Scanning options',
                'desc' => 'The checked HTML tags will be scanned for links on your own blog',
                'type' => 'checkboxes',
                'std' => array(
                    'ahref',
                    'img',
                    'css',
				'javascript'
                ),
                'choices' => array(
                    'ahref' => '&lt;A HREF&gt; links',
                    'img' => '&lt;IMG&gt; tags',
                    'css' => 'Links to CSS files',
                    'javascript' => 'Links to Javascript files'
                )
            ),		  
            array(
                'id' => 'harvest_options',
                'title' => 'Harvesting options',
                'desc' => 'Select the sources to harvest',
                'type' => 'checkboxes',
                'std' => array(
                    'index',
                    'tags',
                    'pages',
                    'posts',
                    'attachments',
                ),
                'choices' => array(
                    'index' => 'Index pages',
                    'tags' => 'Tags',
                    'pages' => 'Pages <a href="http://codex.wordpress.org/Pages"><sup>[?]</sup></a>',
                    'posts' => 'Posts <a href="http://codex.wordpress.org/Posts"><sup>[?]</sup></a>',
                    'attachments' => 'Media attachments'
                )
            ),		  
		  
            array(
                'id' => 'when_to_harvest',
                'title' => 'Rescan when...',
                'desc' => 'Pages will be regenerated on the selected actions',
                'type' => 'checkboxes',
                'std' => array(
                    'trashed_post',
                    'untrashed_post',
                    'deleted_post',
                    'edit_attachment',
                    'edit_category',
                    'post_updated',
                    'wp_insert_post',
                    'publish_page',
				'after_switch_theme',
				'add_attachment',
                ),
                'choices' => array(
                    'publish_page' => 'Page is published',
                    'wp_insert_post' => 'Post is created',
                    'post_updated' => 'Post is updated',
                    'trashed_post' => 'Post sent to trash',
                    'untrashed_post' => 'Post taken out of trash',
                    'deleted_post' => 'Post is deleted ',
                    'edit_attachment' => 'Media attachment is edited',
				'add_attachment' => 'Media attachment is created',
                    'edit_category' => 'Category is edited',
				'after_switch_theme' => 'Theme is changed',
                )
            ),
		  
		  
        )
    );
    
    /*
        // More Settings section
    $wpsf_settings[] = array(
        'section_id' => 'regeneration_settings',
        'section_title' => 'Regenerate',
        'section_order' => 10,
        'fields' => array (
			array(
                'id' => 'refresh_entire_site',
                'title' => 'Regenerate static pages',
                'desc' => "Check here if you want to rebuild the static blog files when you hit 'Save Changes'",
                'type' => 'checkbox',
                'std' => 0
            ),
		  
		  
        )
    );*/
    
    return $wpsf_settings;
}
