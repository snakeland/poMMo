<?php
/**
 * Copyright (C) 2005, 2006, 2007, 2008  Brice Burgess <bhb@iceburg.net>
 * 
 * This file is part of poMMo (http://www.pommo.org)
 * 
 * poMMo is free software; you can redistribute it and/or modify 
 * it under the terms of the GNU General Public License as published 
 * by the Free Software Foundation; either version 2, or any later version.
 * 
 * poMMo is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty
 * of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See
 * the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with program; see the file docs/LICENSE. If not, write to the
 * Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA.
 */
 
 class Pommo_Helper_Maintenance
 {
 	
 	function perform() {
 		Pommo_Helper_Maintenance::memorizeBaseURL();
 		if(is_file(Pommo::$_workDir.'/import.csv'))
 			if (!unlink(Pommo::$_workDir.'/import.csv'))
 				Pommo::kill('Unable to remove import.csv');
 				
 		// truncate error log
 		if (filesize(Pommo::$_workDir . '/ERROR_LOG') > 25)
			rename(Pommo::$_workDir . '/ERROR_LOG', Pommo::$_workDir . '/ERROR_LOG_OLD');
			
		if (!$handle = fopen(Pommo::$_workDir . '/ERROR_LOG','w')) {
			Pommo::$_logger->addErr(Pommo::_T('Can write to ERROR_LOG. Check work directory permissions!'));
			return;
		}
		fwrite($handle,"<?php die(); ?>\n");
		fclose($handle);
 		
 		return true;
 		
 	}
 	// write baseURL to maintenance.php in config file syntax (to be read back by embedded apps)
 	function memorizeBaseURL() {
 		
 		if (!$handle = fopen(Pommo::$_workDir . '/maintenance.php', 'w'))
			Pommo::kill('Unable to prepare maintenance.php for writing');
			
		$fileContent = "<?php die(); ?>\n[baseURL] = \"Pommo::$_baseUrl\"\n";
		
		if (!fwrite($handle, $fileContent)) 
			Pommo::kill('Unable to perform maintenance');
		
		fclose($handle);
 	}
 	
 	function rememberBaseURL() {
 		$config = Pommo_Helper::parseConfig(Pommo::$_workDir . '/maintenance.php');
 		return $config['baseURL'];
 	}
 	
 	// recursively deletes the contents of a directory
 	// if files is passed, only a directories files will be removed
 	function delDir($dirName, $orig = false) {
		if (!$orig)
			$orig = $dirName;
			
		if (empty ($dirName)) 
			return true;
			
		if (file_exists($dirName)) {
			$dir = dir($dirName);
			while ($file = $dir->read()) {
				if ($file != '.' && $file != '..') {
					if (is_dir($dirName . '/' . $file)) {
						Pommo_Helper_Maintenance::delDir($dirName . '/' . $file, $orig);
					} else {
						unlink($dirName . '/' . $file) or die('File ' . $dirName . '/' . $file . ' couldn\'t be deleted!');
					}
				}
			}
			$dir->close();
			if ($dirName != $orig)
				@ rmdir($dirName) or die('Folder ' . $dirName . ' couldn\'t be deleted!');
		} else {
			return false;
		}
		return true;
	}
 }
?>
