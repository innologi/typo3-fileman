<?php
namespace Innologi\Fileman\Service;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013-2016 Frenck Lutke <typo3@innologi.nl>, www.innologi.nl
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
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\File\BasicFileUtility;
use Innologi\Fileman\Domain\Model\File;
/**
 * Facilitates all file-upload interaction.
 *
 * @package fileman
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class FileService implements SingletonInterface {

	/**
	 * $_FILES ext_plugin name
	 *
	 * @var string
	 */
	protected $ext = 'tx_fileman_filelist';

	/**
	 * $_FILES storage name
	 *
	 * @var string
	 */
	protected $storage = 'files';

	/**
	 * $_FILES instance name
	 *
	 * @var string
	 */
	protected $instance = 'file';

	/**
	 * $_FILES property name
	 *
	 * @var string
	 */
	protected $property = 'uploadData';

	/**
	 * Current $_FILES position
	 *
	 * @var string
	 */
	protected $index = NULL;

	/**
	 * Contains isValid() results per index-position once performed
	 *
	 * @var array
	 */
	protected $validated = array();

	/**
	 * True if subtitutes have been searched, false if it hasn't
	 *
	 * @var boolean
	 */
	protected $searchedForSubtitutes = FALSE;
	// @TODO most of its methods are deprecated, so need to replace. Might not need it once we go full-on FAL though.
	/**
	 * Performs some basic file functions
	 *
	 * @var BasicFileUtility
	 */
	protected $fileFunctions;

	/**
	 * @return \TYPO3\CMS\Core\Utility\File\BasicFileUtility
	 */
	protected function getFileFunc() {
		if ($this->fileFunctions === NULL) {
			$this->fileFunctions = GeneralUtility::makeInstance(BasicFileUtility::class);
		}
		return $this->fileFunctions;
	}


	/**
	 * Resets array pointers of $_FILES
	 *
	 * @return void
	 */
	public function reset() {
		if (isset($_FILES[$this->ext]['tmp_name'][$this->storage][$this->instance])) {
			reset($_FILES[$this->ext]['tmp_name'][$this->storage][$this->instance]);
			//it's possible tmp_name is set but name isn't, due to the findSubtitutes() mechanism
			if (isset($_FILES[$this->ext]['name'][$this->storage][$this->instance])) {
				reset($_FILES[$this->ext]['name'][$this->storage][$this->instance]);
			}
		}
		$this->index = NULL;
	}

	/**
	 * Searches through $_POST[$this->ext]['tmpFiles'] for previous $index=>tmp_name values.
	 * Once successful, it places them in $_FILES, so that interaction can occur in multiple stages
	 * f.e. in a multi-step form.
	 *
	 * Note that it relies on finding only FILENAMES of tmp_name, as their directory has been removed for security reasons.
	 * It sets the directory by itself from the appropriate php.ini entry value.
	 *
	 * @return void
	 */
	public function findSubstitutes() {
		$postData = GeneralUtility::_POST($this->ext);
		if (isset($postData['tmpFiles']) && !$this->searchedForSubtitutes) {
			$tmpNames = $postData['tmpFiles'];
			//files once uploaded, have been moved to said location to prevent them from being deleted after the upload script execution
			$tmpDir = ini_get('upload_tmp_dir') ? ini_get('upload_tmp_dir') : sys_get_temp_dir();
			$tmpDir = rtrim(GeneralUtility::fixWindowsFilePath($tmpDir), '/') . '/' . $this->ext . '/';
			foreach ($tmpNames as $index=>$tmpName) {
				if ($this->validateIndex($index) && $this->validateTmpFileName($tmpName)) { //validate the index and tmpName values as they come from (hidden) fields in a form
					$tmpName = $tmpDir . $tmpName;
					$this->setUploadProperty('tmp_name',$tmpName,$index);
					$this->validated[$index] = TRUE; //setting this TRUE because it once has been and can no longer be checked through isValid()
				}
			}
			if (isset($_FILES[$this->ext]['tmp_name'][$this->storage][$this->instance])) {
				ksort($_FILES[$this->ext]['tmp_name'][$this->storage][$this->instance]); #@LOW check if we can do without reset() after a sort
			} else {
				// @TODO throw exception?
			}
		}
		$this->searchedForSubtitutes = TRUE;
	}

	/**
	 * Move the index pointer to the next position, or if reset(), the first
	 *
	 * @return boolean
	 */
	public function next() {
		if (isset($_FILES[$this->ext]['tmp_name'][$this->storage][$this->instance])) {
			$tmpName = current($_FILES[$this->ext]['tmp_name'][$this->storage][$this->instance]);
			if ($tmpName !== FALSE) {
			    $this->index = key($_FILES[$this->ext]['tmp_name'][$this->storage][$this->instance]);
			    next($_FILES[$this->ext]['tmp_name'][$this->storage][$this->instance]);
				return TRUE;
			}
		}
		return FALSE;
	}

	/**
	 * Delete the file at the current index
	 *
	 * @return boolean
	 */
	public function removeFile() {
		return isset($_FILES[$this->ext]['tmp_name'][$this->storage][$this->instance][$this->index][$this->property][0])
			&& is_file($_FILES[$this->ext]['tmp_name'][$this->storage][$this->instance][$this->index][$this->property])
			&& unlink($_FILES[$this->ext]['tmp_name'][$this->storage][$this->instance][$this->index][$this->property]);
	}

	/**
	 * Sets all relevant uploadfile attributes in the $file instance
	 *
	 * @param File $file
	 * @return void
	 */
	public function setFileProperties(File $file) {
		if ($this->getUploadProperty('clear')) {
			// necessary for js uploads, which will maintain their fileUri by themselves
			$file->setFileUri(NULL);
		} else {
			if ($file->getFileUri() === NULL) { //there are cases where fileUri is not set due to the findSubtitutes() mechanism
				$file->setFileUri($this->getUploadProperty('name'));
			}
		}
		// @LOW can these go into the if valid scope?
		$file->setTmpFile($this->getUploadProperty('tmp_name'));
		$file->setIndex($this->index);
	}

	/**
	 * Checks whether a file type is allowed, based on file extensions.
	 *
	 * Considering that apache serves files based on file extension, this check should suffice.
	 *
	 * @param string $allowFileType Allowed filetypes, comma seperated
	 * @param string $denyFileType Denied filetypes, comma seperated
	 * @param string $filename
	 * @return boolean
	 */
	public function isAllowed($allowFileType, $denyFileType, $filename = NULL) {
		$fileInfo = explode('.', (
			$filename !== NULL ? $filename : $this->getUploadProperty('name')
		));
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

	/**
	 * Checks (and stores) whether the current uploadfile is valid.
	 *
	 * @return boolean
	 */
	public function isValid() {
		if (!isset($this->validated[$this->index])) {
			$this->validated[$this->index] = $this->isUploadSuccessful();
		}
		return $this->validated[$this->index];
	}

	/**
	 * Clears file name in upload data.
	 *
	 * Necessary on e.g. upload-failure when the upload itself was successful, e.g. due to file-type-restrictions.
	 *
	 * @return void
	 */
	public function clearFileName() {
		$this->setUploadProperty('name', '', $this->index);
		$this->setUploadProperty('clear', TRUE, $this->index);
	}

	/**
	 * Returns current index position
	 *
	 * @return string
	 */
	public function getIndex() {
		return $this->index;
	}

	/**
	 * Finalize the move of an uploaded file.
	 *
	 * We call it finalize, because it assumes it already has been moved around and should now rely on rename()
	 *
	 * @param File $file
	 * @param string $absDirPath
	 * @return boolean
	 */
	public function finalizeMove(File $file, $absDirPath) {
		if ($this->checkAndCreateDir($absDirPath)) {
			$fileName = $file->getFileUri();
			// if something goes wrong somewhere and we get an empty file name, we'll get exceptions here, so don't even try
			if (isset($fileName[0])) {
				$tmpFile = $file->getTmpFile();
				$finalPath = $this->getFileFunc()->getUniqueName($fileName, $absDirPath);
				//file might have been renamed because of duplicate
				$file->setFileUri(basename($finalPath));
				$success = rename($tmpFile,$finalPath); //I've had some serious caching issues in several browsers when testing changes here, so be wary
				if (!$success) {
					$success = copy($tmpFile, $finalPath);
					try {
						unlink($tmpFile);
					} catch (\Exception $e) {
						// @LOW log?
					}
				}
				// otherwise, permissions might end up non-consistent
				GeneralUtility::fixPermissions($finalPath);
				return $success;
			}
		}
		return FALSE;
	}

	/**
	 * Returns whether the upload was successful, and moves the file to a location where it won't be deleted
	 * right after the upload script has been executed.
	 *
	 * @return boolean
	 */
	protected function isUploadSuccessful() {
		$tmpName = $this->getUploadProperty('tmp_name');
		if (is_uploaded_file($tmpName)) {
			$newTmpName = GeneralUtility::fixWindowsFilePath(dirname($tmpName)) . '/' . $this->ext . '/';
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

	/**
	 * Retrieves an uploadproperty from the $_FILES array
	 *
	 * @param string $uploadProperty e.g. 'tmp_name' or 'name'
	 */
	protected function getUploadProperty($uploadProperty) {
		return isset($_FILES[$this->ext][$uploadProperty])
			? $_FILES[$this->ext][$uploadProperty][$this->storage][$this->instance][$this->index][$this->property]
			: FALSE;
	}

	/**
	 * Sets an uploadproperty in the $_FILES array
	 *
	 * @param string $uploadProperty e.g. 'tmp_name' or 'name'
	 * @param string $value
	 * @param string $index
	 */
	protected function setUploadProperty($uploadProperty, $value, $index) {
		$_FILES[$this->ext][$uploadProperty][$this->storage][$this->instance][$index][$this->property] = $value;
	}

	/**
	 * Validates an index
	 *
	 * @param string $index
	 * @return integer Number of matches
	 */
	protected function validateIndex($index) {
		return preg_match('/^i[0-9]+$/i',$index);
	}

	/**
	 * Validates a basename(tmp_name)
	 *
	 * @param string $tmpFileName
	 * @return integer Number of matches
	 */
	protected function validateTmpFileName($tmpFileName) {
		return preg_match('/^[a-z0-9]+(\.tmp)?$/i',$tmpFileName);
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
	    if (!is_dir($dirpath)) {
	        // if dir doesn't exist, mkdir_deep creates every nonexisting directory
	        GeneralUtility::mkdir_deep($dirpath);
	    }
		return TRUE;
	}

}