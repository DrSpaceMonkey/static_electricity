<?php

namespace FileInterface {

	class AWSInterface extends BaseFileInterface {
		
		private $default_directory;
		private $aws;
		private $client;
		
		public function __construct(){
		
			$this->aws = new \Amazon_Web_Services(plugins_url('amazon-web-services'));
			$this->client = $this->aws->get_client()->get( 's3' );
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
			
			$files = $this->get_file_list($directory);
			
			foreach($files as $file) {
				$this->copy_file_to_s3($file, $directory);
			}
			
			
			//$this->recurse_copy($directory, $destination_directory);
			
		}
		
		private function copy_file_to_s3($path, $base_directory) {
			global $static_electricity_settings;
			$bucket = $static_electricity_settings['aws-target-bucket'];
			
			$base_directory = rtrim($base_directory, '/');
			
			$key_path = str_replace($base_directory, '', $path);
			
			$args = array(
		    'Bucket'     => $bucket,
		    'Key'        => $key_path,
		    'SourceFile' => $path,
		    'ACL'        => 'public-read'
		    );
			$result = $this->client->putObject($args);
			$obj_url = ($result['ObjectURL']);
			
			$this->client->waitUntil('ObjectExists', array(
			    'Bucket' => $bucket,
			    'Key'    => $key_path,
			));
			
			\WP_CLI::success("Uploaded  $path to $obj_url");
			
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
		
		private function get_bucket_list() {						
			$result = $this->client->listBuckets();			
			$retval = array();
			foreach($result['Buckets'] as $bucket) {
				$retval[$bucket['Name']] = $bucket['Name'];
			}
			return $retval;
			
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
					'type'      => 'select',
					'title'     => 'Target bucket',
					'subtitle'  => "S3 Bucket to use",
					'required'  => array('static_electricity_file_interface_select', '=', get_class($this)),
					'validate' => 'not_empty',
					'options'  => $this->get_bucket_list(),
					),				
					array(
					'id'        => 'opt-raw',
					'type'      => 'raw',
					'title'     => 'Target bucket',
					'subtitle'  => "S3 Bucket to use",
					'required'  => array('static_electricity_file_interface_select', '!=', get_class($this)),
					'content' => 'This setting is only required if you are using the S3 storage method',
					),		
			    ),
			);
			return $retval;
		}


		
	}

}