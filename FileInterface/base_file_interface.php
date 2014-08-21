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
		
		private $class_names = array();
		private $interface_classes = array();
		private $default_selection = NULL;
		private $options_array = array();
		
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
			$this->load_file_engines();
		}
		
		public function get_file_engine_sections(){
			$retval = array();
			foreach($this->interface_classes as $c){
				$retval[] = $c->get_redux_options();
			}
			return $retval;
			
		}
		
		private function load_file_engines() {
		
			$namespace_finder = new \NameSpaceFinder();
			$this->class_names = $namespace_finder->getClassesOfNameSpace('FileInterface');
			$this->class_names = array_diff($this->class_names,
				array(
					'FileInterface\BaseFileInterface',
					'FileInterface\FileInterface'				
				));
				
				
			foreach($this->class_names as $c) {
				$interface = new $c();
				$this->options_array[$c] = $interface->get_display_name() ;
				if (is_null($this->default_selection)) $this->default_selection = $interface;
				$this->interface_classes[] = $interface;
			}
		}
		
		public function get_file_engine_selector(){			
			$fields = array(
			    'id'       => 'static-wordpress-file-interface-select',
			    'type'     => 'select',
			    'title'    => 'Storage method', 
			    'subtitle' => '',
			    'desc'     => 'Settings for this storage engine can be set from the menu on the left',
			    // Must provide key => value pairs for select options
			    'options'  => $this->options_array,
			    'select2' 	=> 'allowClear: false',
			    'default'  => get_class($this->default_selection),
			    'validate' => 'not_empty'
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
