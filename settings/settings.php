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
                'id' => 'more-text',
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
                    'link',
				'javascript'
                ),
                'choices' => array(
                    'ahref' => '&lt;A HREF&gt; links',
                    'img' => '&lt;IMG&gt; tags',
                    'link' => 'Links to CSS files',
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
                    'posts'
                ),
                'choices' => array(
                    'index' => 'Index pages',
                    'tags' => 'Tags',
                    'pages' => 'Pages <a href="http://codex.wordpress.org/Pages"><sup>[?]</sup></a>',
                    'posts' => 'Posts <a href="http://codex.wordpress.org/Posts"><sup>[?]</sup></a>'
                )
            ),
		  
		  
        )
    );
    
    return $wpsf_settings;
}
