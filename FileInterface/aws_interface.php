<?php

namespace FileInterface {

	class AWSInterface extends BaseFileInterface {
		
		private $default_directory;
		private $aws;
		private $client;
		
		public function __construct(){
		
			$this->aws = new \Amazon_Web_Services(plugins_url('amazon-web-services'));
			$this->client = $this->aws->get_client();
			$this->default_directory = dirname(__DIR__) . '/static/';
			if (!is_dir($this->default_directory)){
				$this->create_directory($this->default_directory);				
			}
			$this->default_directory = realpath($this->default_directory);
		}
	
	
		public function move_file($source, $destination) {
			rename ( $source , $destination );
		}
		
		
		public function clone_directory_to_destination($directory) {		
			global $static_electricity_settings;
			$destination_directory = $static_electricity_settings['local-file-target-directory'];
			$this->recurse_copy($directory, $destination_directory);
			
		}
		
		private function recurse_copy($src,$dst) { 
		    $dir = opendir($src); 
		    @mkdir($dst); 
		    while(false !== ( $file = readdir($dir)) ) { 
			   if (( $file != '.' ) && ( $file != '..' )) { 
				  if ( is_dir($src . '/' . $file) ) { 
					 $this->recurse_copy($src . '/' . $file,$dst . '/' . $file); 
				  } 
				  else { 
					 copy($src . '/' . $file,$dst . '/' . $file); 
				  } 
			   } 
		    } 
		    closedir($dir); 
		} 

		
		public function copy_file($source, $destination) {	
			if (!directory_exists(dir_name($source)))
				mkdir($destination, 0755, true);
				
			copy ( $source , $destination );
		}
		
		
		public function delete_file($target){	
			unlink($target);
		}	
		
		
		public function create_directory($target){
			mkdir($target, 0755, true);
		}
		
		
		public function get_display_name() {
			return 'S3 bucket';
		}
		
		public function delete_directory($target){	
			FileInterface\FileInterface::deleteDir($dirPath);
		}
		
		public function get_redux_options() {
			$retval = array(
			    'title'   =>  $this->get_display_name(),
			    'icon'    => 'el-icon-cogs',
			    'heading' => 'S3 settings',
			    'desc'    => '',
			    'fields'  => array(		
					array(
					'id'        => 'aws-target-bucket',
					'type'      => 'text',
					'title'     => 'Target directory',
					'subtitle'  => "S3 Bucket to use",
					'desc'      => 'Please use absolute paths, and not relative paths',
					'default'   =>  $this->default_directory,
					),		
			    ),
			);
			return $retval;
		}


		
	}

}