<?php
/*
Plugin Name: WordPress Options
Plugin URI: http://synved.com/wordpress-options/
Description: Easily add options to your themes or plugins with as little or as much coding as you want. Just create an array of your options, the rest is automated. If you need extra flexibility you can then use the powerful API provided to achieve any level of customization.
Author: Synved
Version: 1.4.4
Author URI: http://synved.com/

LEGAL STATEMENTS

NO WARRANTY
All products, support, services, information and software are provided "as is" without warranty of any kind, express or implied, including, but not limited to, the implied warranties of fitness for a particular purpose, and non-infringement.

NO LIABILITY
In no event shall Synved Ltd. be liable to you or any third party for any direct or indirect, special, incidental, or consequential damages in connection with or arising from errors, omissions, delays or other cause of action that may be attributed to your use of any product, support, services, information or software provided, including, but not limited to, lost profits or lost data, even if Synved Ltd. had been advised of the possibility of such damages.
*/

if (!function_exists('synved_wp_option_load'))
{
	function synved_wp_option_load()
	{
		global $plugin;
		
		$path = __FILE__;
	
		if (defined('SYNVED_OPTION_INCLUDE_PATH'))
		{
			$path = SYNVED_OPTION_INCLUDE_PATH;
		}
		else if (isset($plugin))
		{
			/* This is mostly for symlink support */
			$real_plugin = realpath($plugin);
			
			if (strtolower($real_plugin) == strtolower(__FILE__))
			{
				$path = $plugin;
			}
		}
	
		$dir = dirname($path) . DIRECTORY_SEPARATOR;
	
		if (!function_exists('synved_plugout_module_import'))
		{
			include($dir . 'synved-plugout' . DIRECTORY_SEPARATOR . 'synved-plugout.php');
		}

		/* Register used modules */
		synved_plugout_module_register('synved-option');
		synved_plugout_module_path_add('synved-option', 'core', $dir . 'synved-option');
	
		/* Import modules */
		synved_plugout_module_import('synved-option');
	}

	synved_wp_option_load();
}

synved_plugout_module_path_add('synved-option', 'addon', dirname((defined('SYNVED_OPTION_INCLUDE_PATH') ? SYNVED_OPTION_INCLUDE_PATH : __FILE__)) . '/synved-option/addons');

?>
