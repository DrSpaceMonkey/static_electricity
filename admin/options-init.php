<?php

/**
ReduxFramework Sample Config File
For full documentation, please visit: https://docs.reduxframework.com
* */

if (!class_exists('admin_folder_Redux_Framework_config')) {

	class admin_folder_Redux_Framework_config {

		public $args        = array();
		public $sections    = array();
		public $theme;
		public $ReduxFramework;

		public function __construct() {

			if (!class_exists('ReduxFramework')) {
				return;
			}

			// This is needed. Bah WordPress bugs.  ;)
			if ( true == Redux_Helpers::isTheme( __FILE__ ) ) {
				$this->initSettings();
			} else {
				add_action('plugins_loaded', array($this, 'initSettings'), 10);
			}

		}

		public function initSettings() {

			// Just for demo purposes. Not needed per say.
			$this->theme = wp_get_theme();

			// Set the default arguments
			$this->setArguments();

			// Set a few help tabs so you can see how it's done
			$this->setHelpTabs();

			// Create the sections and fields
			$this->setSections();

			if (!isset($this->args['opt_name'])) { // No errors please
				return;
			}

			// If Redux is running as a plugin, this will remove the demo notice and links
			add_action( 'redux/loaded', array( $this, 'remove_demo' ) );
			
			// Function to test the compiler hook and demo CSS output.
			// Above 10 is a priority, but 2 in necessary to include the dynamically generated CSS to be sent to the function.
			//add_filter('redux/options/'.$this->args['opt_name'].'/compiler', array( $this, 'compiler_action' ), 10, 2);
			
			// Change the arguments after they've been declared, but before the panel is created
			//add_filter('redux/options/'.$this->args['opt_name'].'/args', array( $this, 'change_arguments' ) );
			
			// Change the default value of a field after it's been set, but before it's been useds
			//add_filter('redux/options/'.$this->args['opt_name'].'/defaults', array( $this,'change_defaults' ) );
			
			// Dynamically add a section. Can be also used to modify sections/fields
			//add_filter('redux/options/' . $this->args['opt_name'] . '/sections', array($this, 'dynamic_section'));

			$this->ReduxFramework = new ReduxFramework($this->sections, $this->args);
		}

		/**

		This is a test function that will let you see when the compiler hook occurs.
		It only runs if a field	set with compiler=>true is changed.

	* */
		function compiler_action($options, $css) {
			//echo '<h1>The compiler hook has run!';
			//print_r($options); //Option values
			//print_r($css); // Compiler selector CSS values  compiler => array( CSS SELECTORS )

			/*
		// Demo of how to use the dynamic CSS and write your own static CSS file
		$filename = dirname(__FILE__) . '/style' . '.css';
		global $wp_filesystem;
		if( empty( $wp_filesystem ) ) {
			require_once( ABSPATH .'/wp-admin/includes/file.php' );
		WP_Filesystem();
		}

		if( $wp_filesystem ) {
			$wp_filesystem->put_contents(
				$filename,
				$css,
				FS_CHMOD_FILE // predefined mode settings for WP files
			);
		}
		*/
		}

		/**

		Custom function for filtering the sections array. Good for child themes to override or add to the sections.
		Simply include this function in the child themes functions.php file.

		NOTE: the defined constants for URLs, and directories will NOT be available at this point in a child theme,
		so you must use get_template_directory_uri() if you want to use any of the built in icons

	* */
		function dynamic_section($sections) {
			//$sections = array();
			$sections[] = array(
			'title' => __('Section via hook', 'redux-framework-demo'),
			'desc' => __('<p class="description">This is a section created by adding a filter to the sections array. Can be used by child themes to add/remove sections from the options.</p>', 'redux-framework-demo'),
			'icon' => 'el-icon-paper-clip',
			// Leave this as a blank section, no options just some intro text set above.
			'fields' => array()
			);

			return $sections;
		}

		/**

		Filter hook for filtering the args. Good for child themes to override or add to the args array. Can also be used in other functions.

	* */
		function change_arguments($args) {
			//$args['dev_mode'] = true;

			return $args;
		}

		/**

		Filter hook for filtering the default value of any given field. Very useful in development mode.

	* */
		function change_defaults($defaults) {
			$defaults['str_replace'] = 'Testing filter hook!';

			return $defaults;
		}

		// Remove the demo link and the notice of integrated demo from the redux-framework plugin
		function remove_demo() {

			// Used to hide the demo mode link from the plugin page. Only used when Redux is a plugin.
			if (class_exists('ReduxFrameworkPlugin')) {
				remove_filter('plugin_row_meta', array(ReduxFrameworkPlugin::instance(), 'plugin_metalinks'), null, 2);

				// Used to hide the activation notice informing users of the demo panel. Only used when Redux is a plugin.
				remove_action('admin_notices', array(ReduxFrameworkPlugin::instance(), 'admin_notices'));
			}
		}

		public function setSections() {

			/**
		Used within different fields. Simply examples. Search for ACTUAL DECLARATION for field examples
		* */
			// Background Patterns Reader
			$sample_patterns_path   = ReduxFramework::$_dir . '../sample/patterns/';
			$sample_patterns_url    = ReduxFramework::$_url . '../sample/patterns/';
			$sample_patterns        = array();
			$home_url               = home_url();

			if (is_dir($sample_patterns_path)) :

			if ($sample_patterns_dir = opendir($sample_patterns_path)) :
			$sample_patterns = array();

			while (( $sample_patterns_file = readdir($sample_patterns_dir) ) !== false) {

				if (stristr($sample_patterns_file, '.png') !== false || stristr($sample_patterns_file, '.jpg') !== false) {
					$name = explode('.', $sample_patterns_file);
					$name = str_replace('.' . end($name), '', $sample_patterns_file);
					$sample_patterns[]  = array('alt' => $name, 'img' => $sample_patterns_url . $sample_patterns_file);
				}
			}
			endif;
			endif;

			ob_start();

			$ct             = wp_get_theme();
			$this->theme    = $ct;
			$item_name      = $this->theme->get('Name');
			$tags           = $this->theme->Tags;
			$screenshot     = $this->theme->get_screenshot();
			$class          = $screenshot ? 'has-screenshot' : '';

			$customize_title = sprintf(__('Customize &#8220;%s&#8221;', 'redux-framework-demo'), $this->theme->display('Name'));
			
			?>
			<div id="current-theme" class="<?php echo esc_attr($class); ?>">
			<?php if ($screenshot) : ?>
			<?php if (current_user_can('edit_theme_options')) : ?>
			<a href="<?php echo wp_customize_url(); ?>" class="load-customize hide-if-no-customize" title="<?php echo esc_attr($customize_title); ?>">
			<img src="<?php echo esc_url($screenshot); ?>" alt="<?php esc_attr_e('Current theme preview'); ?>" />
			</a>
			<?php endif; ?>
			<img class="hide-if-customize" src="<?php echo esc_url($screenshot); ?>" alt="<?php esc_attr_e('Current theme preview'); ?>" />
			<?php endif; ?>

			<h4><?php echo $this->theme->display('Name'); ?></h4>

			<div>
			<ul class="theme-info">
			<li><?php printf(__('By %s', 'redux-framework-demo'), $this->theme->display('Author')); ?></li>
			<li><?php printf(__('Version %s', 'redux-framework-demo'), $this->theme->display('Version')); ?></li>
			<li><?php echo '<strong>' . __('Tags', 'redux-framework-demo') . ':</strong> '; ?><?php printf($this->theme->display('Tags')); ?></li>
			</ul>
			<p class="theme-description"><?php echo $this->theme->display('Description'); ?></p>
			<?php
			if ($this->theme->parent()) {
				printf(' <p class="howto">' . __('This <a href="%1$s">child theme</a> requires its parent theme, %2$s.') . '</p>', __('http://codex.wordpress.org/Child_Themes', 'redux-framework-demo'), $this->theme->parent()->display('Name'));
			}
			?>

			</div>
			</div>

			<?php
			$item_info = ob_get_contents();

			ob_end_clean();

			$sampleHTML = '';
			if (file_exists(dirname(__FILE__) . '/info-html.html')) {
				/** @global WP_Filesystem_Direct $wp_filesystem  */
				global $wp_filesystem;
				if (empty($wp_filesystem)) {
					require_once(ABSPATH . '/wp-admin/includes/file.php');
					WP_Filesystem();
				}
				$sampleHTML = $wp_filesystem->get_contents(dirname(__FILE__) . '/info-html.html');
			}
			
			global $static_file_interface;
			
			$this->sections[] = array(
			'type' => 'divide',
			);


			$this->sections[] = array(
			'icon'      => 'el-icon-cogs',
			'title'     => 'Settings',
			'fields'    => array(
			array(
			'id'        => 'replace_uri_in_links',
			'type'      => 'switch',
			'title'     => 'Replace URIs',
			'subtitle'  => "If checked, the hostname ($home_url) in links will be replaced with <em>URI prefix</em>.",
			'default'   => 1,
			'on'        => 'Enabled',
			'off'       => 'Disabled',
			),
			array(
			'id'        => 'replacement_uri_prefix',
			'type'      => 'text',
			'required'  => array('replace_uri_in_links', '=', '1'),
			'title'     => 'URI prefix',
			'subtitle'  => "Hostname to replace $home_url with",
			'desc'      => 'Use a single slash (/) to turn absolute paths into relative paths',
			'default'   => $home_url,
			),			
			array(
			'id'        => 'index_page_filename',
			'type'      => 'text',
			'title'     => 'Index page filename',
			'subtitle'  => '',
			'desc'      => 'Index files will be given this name',
			'default'   => 'index.html',
			),					
			array(
			'id'        => 'static_electricity_working_directory',
			'type'      => 'text',
			'title'     => 'Working directory',
			'subtitle'  => "Directory where the static files will be built",
			'desc'      => 'Please use absolute paths, and not relative paths',
			'default'   => trailingslashit(sys_get_temp_dir()) . 'static_electricity/',
			),			
			$static_file_interface->get_file_engine_selector(),
			array(
			'id'        => 'harvest_options',
			'type'      => 'checkbox',
			'title'     => 'Harvesting options',
			'desc'  => '',
			'subtitle'      => 'Select the sources to harvest',
			
			//Must provide key => value pairs for multi checkbox options
			'options'   => array(
			'index' => 'Index pages', 
			'tags' => 'Tags', 
			'pages' => 'Pages <a href="http://codex.wordpress.org/Pages"><sup>[?]</sup></a>',
			'posts' => 'Posts <a href="http://codex.wordpress.org/Posts"><sup>[?]</sup></a>',
			'attachments' => 'Media attachments',
			),
			
			//See how std has changed? you also don't need to specify opts that are 0.
			'default'   => array(
			'index' => '1', 
			'tags' => '1', 
			'pages' => '1', 
			'posts' => '1',
			'attachments' => '1'
			)
			),
			
			array(
			'id' => 'scanning_options',
			'title' => 'Scanning options',
			'subtitle' => 'The checked HTML tags will be scanned for links on your own blog',
			'type' => 'checkbox',
			'default' => array(
			'ahref' => '1', 
			'img' => '1', 
			'css' => '1', 
			'javascript' => '1', 
			),
			'options' => array(
			'ahref' => '&lt;A HREF&gt; links',
			'img' => '&lt;IMG&gt; tags',
			'css' => 'Links to CSS files',
			'javascript' => 'Links to Javascript files'
			)
			),

			array(
			'id' => 'when_to_harvest',
			'title' => 'Trigger rescan on...',
			'subtitle' => 'Pages will be regenerated on the selected actions',
			'type' => 'checkbox',
			'default' => array(
			'trashed_post' => '1', 
			'untrashed_post' => '1', 
			'deleted_post' => '1', 
			'edit_attachment' => '1', 
			'edit_category' => '1', 
			'post_updated' => '1', 
			'wp_insert_post' => '1', 
			'publish_page' => '1', 
			'after_switch_theme' => '1', 
			'add_attachment' => '1', 
			),
			'options' => array(
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

                    array(
                        'id'        => 'clear_checksum',
                        'type'      => 'switch',
                        'title'     => 'Rebuild entire blog',
                        'desc'      => 'If you set this value, the entire static site will be rebuilt. Otherwise, files will only be updated if the checksum has changed. <p>Do this if your theme has changed.',
                        'default'   => false,
                    ),	
                    array(
                        'id'        => 'rescan_blog',
                        'type'      => 'switch',
                        'title'     => 'Rescan blog',
                        'desc'      => 'To rescan the entire blog, turn this on and hit "Save Changes"',
                        'default'   => false,
                    ),			
			)
			);
			
			$file_engines = $static_file_interface->get_file_engine_sections();
				foreach($file_engines as $sec) {
					$this->sections[] = $sec;
			}
			
			/**
		*  Note here I used a 'heading' in the sections array construct
		*  This allows you to use a different title on your options page
		* instead of reusing the 'title' value.  This can be done on any
		* section - kp
		*/
		}

		public function setHelpTabs() {
/*
			// Custom page help tabs, displayed using the help API. Tabs are shown in order of definition.
			$this->args['help_tabs'][] = array(
			'id'        => 'redux-help-tab-1',
			'title'     => __('Theme Information 1', 'redux-framework-demo'),
			'content'   => __('<p>This is the tab content, HTML is allowed.</p>', 'redux-framework-demo')
			);

			$this->args['help_tabs'][] = array(
			'id'        => 'redux-help-tab-2',
			'title'     => __('Theme Information 2', 'redux-framework-demo'),
			'content'   => __('<p>This is the tab content, HTML is allowed.</p>', 'redux-framework-demo')
			);

			// Set the help sidebar
			$this->args['help_sidebar'] = __('<p>This is the sidebar content, HTML is allowed.</p>', 'redux-framework-demo');
			*/
		}

		/**

		All the possible arguments for Redux.
		For full documentation on arguments, please refer to: https://github.com/ReduxFramework/ReduxFramework/wiki/Arguments

	* */
		public function setArguments() {

			$theme = wp_get_theme(); // For use with some settings. Not necessary.

			$this->args = array(
			'opt_name' => 'static_electricity_settings',
			'global_variable' => 'static_electricity_settings',			
			'display_name' => 'Static Electricity',
			'page_slug' => 'static_electricity_options',
			'page_title' => 'Static Electricity',
			'update_notice' => true,
			'intro_text' => '',
			'footer_text' => '',
			'admin_bar' => true,
			'menu_type' => 'menu',
			'allow_sub_menu' => false,
			'menu_title' => 'Static Electricity',
			'page_parent_post_type' => 'your_post_type',
			'customizer' => true,
			'default_show' => true,
			'default_mark' => '',
			'hints' => 
			array(
			'icon' => 'el-icon-question-sign',
			'icon_position' => 'right',
			'icon_size' => 'normal',
			'tip_style' => 
			array(
			'color' => 'light',
			),
			'tip_position' => 
			array(
			'my' => 'top left',
			'at' => 'bottom right',
			),
			'tip_effect' => 
			array(
			'show' => 
			array(
			'duration' => '500',
			'event' => 'mouseover',
			),
			'hide' => 
			array(
			'duration' => '500',
			'event' => 'mouseleave unfocus',
			),
			),
			),
			'output' => true,
			'output_tag' => true,
			'open_expanded' => false,
			'compiler' => true,
			#'page_icon' => 'el-icon-cogs',
			'page_icon' => 'icon-themes',
			'page_permissions' => 'manage_options',
			'save_defaults' => true,
			'show_import_export' => false,
			'transient_time' => '3600',
			'network_sites' => true,
			'admin_bar_icon' => 'dashicons-admin-generic'
			);


		}

	}

	global $reduxConfig;
	$reduxConfig = new admin_folder_Redux_Framework_config();
}

/**
Custom function for the callback referenced above
*/
if (!function_exists('admin_folder_my_custom_field')):
function admin_folder_my_custom_field($field, $value) {
	print_r($field);
	echo '<br/>';
	print_r($value);
}
endif;

/**
Custom function for the callback validation referenced above
* */
if (!function_exists('admin_folder_validate_callback_function')):
function admin_folder_validate_callback_function($field, $value, $existing_value) {
	$error = false;
	$value = 'just testing';

	/*
		do your validation

		if(something) {
		$value = $value;
		} elseif(something else) {
		$error = true;
		$value = $existing_value;
		$field['msg'] = 'your custom error message';
		}
	*/

	$return['value'] = $value;
	if ($error == true) {
		$return['error'] = $field;
	}
	return $return;
}
endif;
