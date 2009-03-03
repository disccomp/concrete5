<?
/**
 * @package Helpers
 * @category Concrete
 * @author Andrew Embler <andrew@concrete5.org>
 * @copyright  Copyright (c) 2003-2008 Concrete5. (http://www.concrete5.org)
 * @license    http://www.concrete5.org/license/     MIT License
 */

/**
 * Internal functions useful for working with files.
 * @package Helpers
 * @category Concrete
 * @author Andrew Embler <andrew@concrete5.org>
 * @copyright  Copyright (c) 2003-2008 Concrete5. (http://www.concrete5.org)
 * @license    http://www.concrete5.org/license/     MIT License
 */

	defined('C5_EXECUTE') or die(_("Access Denied."));
	class ConcreteFileHelper {
	
		/** 
		 * Given a file's prefix and its name, returns a path to the.
		 * Can optionally create the path if this is the first time doing this operation for this version.
		 */
		public function getSystemPath($prefix, $filename, $createDirectories = false) {
			return $this->mapSystemPath($prefix, $filename, $createDirectories);
		}
		
		public function getThumbnailSystemPath($prefix, $filename, $level, $createDirectories = false) {
			switch($level) {
				case 2:
					$base = DIR_FILES_UPLOADED_THUMBNAILS_LEVEL2;
					break;
				case 3:
					$base = DIR_FILES_UPLOADED_THUMBNAILS_LEVEL3;
					break;
				default: // level 1
					$base = DIR_FILES_UPLOADED_THUMBNAILS;
					break;
			}
			
			$hi = Loader::helper('file');
			$filename = $hi->replaceExtension($filename, 'jpg');
			return $this->mapSystemPath($prefix, $filename, $createDirectories, $base);
		}

		public function getRelativePath($prefix, $filename ) { 
			$hi = Loader::helper('file');
			return $this->mapSystemPath($prefix, $filename, false, REL_DIR_FILES_UPLOADED);
		}
		
		public function getFileRelativePath($prefix, $filename ) { 	
			return $this->getRelativePath($prefix, $filename);
		}
		
		public function getThumbnailRelativePath($prefix, $filename, $level) {
			switch($level) {
				case 2:
					$base = REL_DIR_FILES_UPLOADED_THUMBNAILS_LEVEL2;
					break;
				case 3:
					$base = REL_DIR_FILES_UPLOADED_THUMBNAILS_LEVEL3;
					break;
				default: // level 1
					$base = REL_DIR_FILES_UPLOADED_THUMBNAILS;
					break;
			}
			
			$hi = Loader::helper('file');
			$filename = $hi->replaceExtension($filename, 'jpg');
			return $this->mapSystemPath($prefix, $filename, $createDirectories, $base);
		}
		
		private function mapSystemPath($prefix, $filename, $createDirectories = false, $base = DIR_FILES_UPLOADED) {
			$d1 = substr($prefix, 0, 4);
			$d2 = substr($prefix, 4, 4);
			$d3 = substr($prefix, 8);
			
			if ($createDirectories) {
				if (!is_dir($base . '/' . $d1 . '/' . $d2 . '/' . $d3)) {
					mkdir($base . '/' . $d1 . '/' . $d2 . '/' . $d3, 0777, TRUE);
				}
			}
			
			$path = $base . '/' . $d1 . '/' . $d2 . '/' . $d3 . '/' . $filename;
			return $path;
		}
		public function getIncomingDirectoryContents() {
			$incoming_file_information = array();
			
			if (is_dir(DIR_FILES_INCOMING)) {
			    if ($incoming_file_handle = opendir(DIR_FILES_INCOMING)) {
			        $cnt = 0;
					
					while (($file = readdir($incoming_file_handle)) !== false) {
						if($file == '.' || $file == '..')
							continue;
						
						$current_file_stats = array();
						$current_file_stats = stat(DIR_FILES_INCOMING .'/'. $file);
						
						$incoming_file_information[$cnt]['name'] = $file;
			            $incoming_file_information[$cnt]['size'] = floor($current_file_stats[7] / 1000); // Fetch for Kb
			        
						$cnt++;
					}
			        closedir($incoming_file_handle);
				}
			}			
		
			return $incoming_file_information;
		}
		const REGEX_INVALID_EXTENSION_CHARS = '{[^a-z0-9]}i';
		/**
		 * Serizlies an array of strings into format suitable for multi-uploader
		 *
		 * example for format:
		 * '*.flv;*.jpg;*.gif;*.jpeg;*.ico;*.docx;*.xla;*.png;*.psd;*.swf;*.doc;*.txt;*.xls;*.csv;*.pdf;*.tiff;*.rtf;*.m4a;*.mov;*.wmv;*.mpeg;*.mpg;*.wav;*.avi;*.mp4;*.mp3;*.qt;*.ppt;*.kml'
		 * @param array $types
		 * @return string
		 */	
		public function serializeUploadFileExtentions($types){
			$serialized = '';
			$types = preg_replace(self::REGEX_INVALID_EXTENSION_CHARS,'',$types);
			foreach ($types as $type) {				
				$serialized .= '*.'.$type.';';
			}
			//removing trailing ; unclear if multiupload will choke on that or not
			$serialized = substr ($serialized, 0, strlen($serialized)-1);
			return $serialized;			
		}
		
		/**
		 * UnSerizlies an array of strings from format suitable for multi-uploader
		 *
		 * example for format:
		 * '*.flv;*.jpg;*.gif;*.jpeg;*.ico;*.docx;*.xla;*.png;*.psd;*.swf;*.doc;*.txt;*.xls;*.csv;*.pdf;*.tiff;*.rtf;*.m4a;*.mov;*.wmv;*.mpeg;*.mpg;*.wav;*.avi;*.mp4;*.mp3;*.qt;*.ppt;*.kml'
		 * @param string $types
		 * @return array
		 */					
		public function unSerializeUploadFileExtentions($types){
			//split by semi-colon
			$types = preg_split('{;}',$types,null,PREG_SPLIT_NO_EMPTY);
			$types = preg_replace(self::REGEX_INVALID_EXTENSION_CHARS,'',$types);
			return $types;
		}		
	
	}
	
?>