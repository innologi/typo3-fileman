<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Frenck Lutke <frenck@innologi.nl>, www.innologi.nl
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Facilitates file-upload interaction.
 *
 * @package fileman
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Tx_Fileman_Service_FileService implements t3lib_Singleton {
	#@TODO doc
	//$_FILES property names
	protected $ext = 'tx_fileman_filelist'; //ext_plugin name
	protected $storage = 'files'; //storage name
	protected $instance = 'file'; //instance name
	protected $property = 'fileUri'; //property name

	protected $index = NULL;
	protected $validated = array();
	protected $searchedForSubtitutes = FALSE;

	/**
	 * @var t3lib_basicFileFunctions
	 */
	protected $fileFunctions;

	/**
	 *
	 * @param t3lib_basicFileFunctions $fileFunctions
	 * @return void
	 */
	public function injectFileFunctions(t3lib_basicFileFunctions $fileFunctions) {
		$this->fileFunctions = $fileFunctions;
	}


	public function reset() {
		reset($_FILES[$this->ext]['tmp_name'][$this->storage][$this->instance]);
		if (isset($_FILES[$this->ext]['name'][$this->storage][$this->instance])) {
			reset($_FILES[$this->ext]['name'][$this->storage][$this->instance]);
		}
		$this->index = NULL;
	}

	public function findSubstitutes() {
		if (isset($_POST[$this->ext]['tmpFiles']) && !$this->searchedForSubtitutes) {
			$tmpNames = $_POST[$this->ext]['tmpFiles'];
			$tmpDir = t3lib_div::fixWindowsFilePath(ini_get('upload_tmp_dir')) . '/' . $this->ext . '/';
			foreach ($tmpNames as $index=>$tmpName) {
				if ($this->validateIndex($index) && $this->validateTmpName($tmpName)) {
					$tmpName = $tmpDir . $tmpName;
					$this->setUploadProperty('tmp_name',$tmpName,$index);
					$this->validated[$index] = TRUE;
				}
			}
			ksort($_FILES[$this->ext]['tmp_name'][$this->storage][$this->instance]);
		}
		$this->searchedForSubtitutes = TRUE;
	}

	public function next() {
		$tmpNameContainer = each($_FILES[$this->ext]['tmp_name'][$this->storage][$this->instance]);
		if ($tmpNameContainer !== FALSE) {
			$this->index = $tmpNameContainer['key'];
			return TRUE;
		}
		return FALSE;
	}

	public function setFileProperties(Tx_Fileman_Domain_Model_File $file) {
		if ($file->getFileUri() === NULL) {
			$file->setFileUri($this->getUploadProperty('name'));
		}
		$file->setTmpFile($this->getUploadProperty('tmp_name'));
		$file->setIndex($this->index);
	}

	public function isAllowed($allowFileType, $denyFileType) {
		$fileInfo = explode('.',$this->getUploadProperty('name'));
		$fileExt = end($fileInfo);
		$allowed = TRUE;

		if (isset($allowFileType[0])) {
			$fileTypes = explode(',',$allowFileType);
			$allowed = in_array($fileExt,$fileTypes);
		} elseif (isset($denyFileType[0])) {
			$fileTypes = explode(',',$denyFileType);
			$allowed = !in_array($fileExt,$fileTypes);
		}

		return $allowed;
	}

	public function isValid() {
		if (!isset($this->validated[$this->index])) {
			$this->validated[$this->index] = $this->isUploadSuccessful();
		}
		return $this->validated[$this->index];
	}

	public function hasValidated() {
		return isset($this->validated[$this->index]) && $this->validated[$this->index];
	}

	public function getIndex() {
		return $this->index;
	}

	public function finalizeMove($file, $absDirPath) {
		if ($this->checkAndCreateDir($absDirPath)) {
			$fileName = $file->getFileUri();
			$tmpFile = $file->getTmpFile();
			$finalPath = $this->fileFunctions->getUniqueName($fileName, $absDirPath);
			//file might have been renamed because of duplicate
			$file->setFileUri(basename($finalPath)); #@TODO godver de godver de godver, TCA group verwacht hier de filename, niet het pad! dus voor nu aangepast
			return rename($tmpFile,$finalPath);
		}
		return FALSE;
	}

	#@TODO doc
	protected function isUploadSuccessful() {
		$tmpName = $this->getUploadProperty('tmp_name');
		if (is_uploaded_file($tmpName)) {
			$newTmpName = t3lib_div::fixWindowsFilePath(dirname($tmpName)) . $this->ext . '/';
			if ($this->checkAndCreateDir($newTmpName)) {
				$newTmpName .= basename($tmpName);
				if (move_uploaded_file($tmpName,$newTmpName)) {
					$this->setUploadProperty('tmp_name', $newTmpName, $this->index);
					return TRUE;
				}
			}
		}

		return FALSE;
	}

	protected function getUploadProperty($uploadProperty) {
		return $_FILES[$this->ext][$uploadProperty][$this->storage][$this->instance][$this->index][$this->property];
	}

	protected function setUploadProperty($uploadProperty, $value, $index) {
		$_FILES[$this->ext][$uploadProperty][$this->storage][$this->instance][$index][$this->property] = $value;
	}

	protected function validateIndex($index) {
		return preg_match('/^[a-z0-9]+$/i',$index);
	}

	protected function validateTmpFileName($tmpFileName) {
		return preg_match('/^[a-z1-9]+\.tmp$/i',$tmpFileName);
	}

	protected function validateName() {
		#@TODO make this
	}


	/**
	 * Checks if a directory exists. If it doesn't, it attempts to create it one directory at a time.
	 *
	 * @param	string		$dirpath	The path to the directory
	 * @return	boolean		True on success, false on failure
	 */
	protected function checkAndCreateDir($dirpath) {
		//split the dirpath for use by mkdir_deep
		$matches = array();
		$pattern = '=^(([a-z]:)?/)(.*)$=i'; //.. thus windows-paths are assumed to have been corrected!
		preg_match($pattern,$dirpath,$matches);
		//if dir doesn't exist, mkdir_deep creates every nonexisting directory from its second argument..
		if (!is_dir($dirpath) && !is_null(t3lib_div::mkdir_deep($matches[1],$matches[3]))) {
			//mkdir_deep only returns something on errors
			return FALSE;
		}
		return TRUE;
	}

}
?>