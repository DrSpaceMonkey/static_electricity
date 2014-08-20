<?php

namespace FileInterface {

	class LocalFileInterface extends BaseFileInterface {


		public function move_file($source, $destination) {
			rename ( $source , $destination );
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
			return 'Local file';
		}
		
		public function delete_directory($target){	
			$this->deleteDir($target);
		}
		
		public function get_redux_options() {
		}


		private function deleteDir($dirPath) {
		    if (! is_dir($dirPath)) {
			   throw new InvalidArgumentException("$dirPath must be a directory");
		    }
		    if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
			   $dirPath .= '/';
		    }
		    $files = glob($dirPath . '*', GLOB_MARK);
		    foreach ($files as $file) {
			   if (is_dir($file)) {
				  self::deleteDir($file);
			   } else {
				  unlink($file);
			   }
		    }
		    rmdir($dirPath);
		}
		
	}

}