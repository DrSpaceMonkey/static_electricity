<?php

namespace FileInterface;

	abstract class BaseFileInterface {
		abstract public function move_file($source, $destination);	
		abstract public function copy_file($source, $destination);	
		abstract public function delete_file($target);	
		abstract public function create_directory($target);
		abstract public function delete_directory($target);
		abstract public function get_redux_options();
		abstract public function get_display_name();
	}
	
	class FileInterface {
		private $namespace_classes;
		public function __construct(){	
			$directory = __DIR__;		
			require_once trailingslashit($directory) . 'NameSpaceFinder.php';
			$scanned_directory = array_diff(scandir($directory), array('..', '.'));
			foreach($scanned_directory as $object_name) {
				$full_path = trailingslashit($directory) . $object_name;
				if (is_file($full_path) && FileInterface::endsWith($full_path, '.php')) {
					require_once $full_path;
				}
			}
			$namespace_finder = new \NameSpaceFinder();
			$this->namespace_classes = $namespace_finder->getClassesOfNameSpace('FileInterface');
			$this->namespace_classes = array_diff($this->namespace_classes,
				array(
					'FileInterface\BaseFileInterface',
					'FileInterface\FileInterface'				
				));
			
		}
		
		public function get_redux_section(){
		
			$options_array = array();
			print_r($this->namespace_classes);
			$x = 0;
			foreach($this->namespace_classes as $c) {
				$interface = new $c();
				print_r($interface->get_display_name());
				$options_array[$c] = $interface->get_display_name() ;
				if (!is_set($default_selection)) $default_selection = $c;
			}
			
			print_r($options_array);
			
			$fields = array(
			    'id'       => 'static-wordpress-file-interface-select',
			    'type'     => 'select',
			    'title'    => 'Storage method', 
			    'subtitle' => '',
			    'desc'     => '',
			    // Must provide key => value pairs for select options
			    'options'  => $options_array,
			    'default'  => $default_selection,
			);
			
			return $fields;
		}
		
		public function the_chosen_one(){
			
		}
		
		
		static function endsWith($haystack, $needle)
		{
			$length = strlen($needle);
			if ($length == 0) {
				return true;
			}
			return (substr($haystack, -$length) === $needle);
		}

		
	}


global $static_file_interface;
$static_file_interface = new FileInterface();