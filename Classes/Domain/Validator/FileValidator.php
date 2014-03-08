<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012-2013 Frenck Lutke <frenck@innologi.nl>, www.innologi.nl
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
 * Handles additional domain validations for the File domain.
 *
 * @package fileman
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Tx_Fileman_Domain_Validator_FileValidator extends Tx_Extbase_Validation_Validator_AbstractValidator {

	/**
	 * TypoScript settings
	 *
	 * @var array
	 */
	protected $settings;

	/**
	 * Necessary to resolve $settings
	 *
	 * @var Tx_Extbase_Configuration_ConfigurationManager
	 */
	protected $configurationManager;

	/**
	 * File service
	 *
	 * @var Tx_Fileman_Service_FileService
	 */
	protected $fileService;

	/**
	 * Injects the File Service
	 *
	 * @param Tx_Fileman_Service_FileService $fileService
	 * @return void
	 */
	public function injectFileService(Tx_Fileman_Service_FileService $fileService) {
		$this->fileService = $fileService;
	}

	/**
	 * Injects the Configuration manager and initializes $settings
	 *
	 * @param Tx_Extbase_Configuration_ConfigurationManager $configurationManager
	 * @return void
	 */
	public function injectConfigurationManager(Tx_Extbase_Configuration_ConfigurationManager $configurationManager) {
		$this->configurationManager = $configurationManager;
		$this->settings = $this->configurationManager->getConfiguration(Tx_Extbase_Configuration_ConfigurationManager::CONFIGURATION_TYPE_SETTINGS);
	}



	#@FIX delete failed files?
	/**
	 * Does some specific file domain validation.
	 *
	 * @param mixed $file
	 * @return	boolean
	 */
	public function isValid($file) {
		$valid = FALSE;
		$extName = 'Fileman';
		$this->errors = array();
		$errorMessage = '';
		$errorCode = 0;

		//only proceed if instance matches and an actual file upload took place
		if ($file instanceof Tx_Fileman_Domain_Model_File) {
			if ($this->fileService->next()) {
				$file->setIndex($this->fileService->getIndex()); //we need this regardless, to bind errors from this and other validators to the right file
				// NULL @ first-time create, NOT NULL after validation error
				if ($file->getFileUri() === NULL) {
					if (!$this->fileService->isAllowed($this->settings['allowFileType'],$this->settings['denyFileType'])) {
						//the file type is prohibited by configuration
						$errorCode = 40750133701;
					} elseif (!$this->fileService->isValid()) {
						//there was no file uploaded or something went wrong
						$errorCode = 40750133702;
					} else {
						$valid = TRUE;
					}
				} else {
					//if not empty, a successful validation had already taken place
					$valid = TRUE;
				}
			} elseif ($file->getFileUri() !== NULL) {
				//edit action
				$valid = TRUE;
			} else {
				#@LOW error
				//no FILES and no fileUri, something is fucked
			}
		} else {
			#@LOW error
			//not a FILE
		}


		if ($valid) {
			//assign uploadfile attributes to the $file entry
			$this->fileService->setFileProperties($file);
			//replace empty title
			$title = $file->getAlternateTitle();
			if (empty($title)) {
				$file->setAlternateTitle($file->getFileUri());
			}
		} else {
			// uploadData errors might ensue after a successful upload, so clear the file name so we don't lose the upload-field
			$this->fileService->clearFileName();
			//setup error message
			$propertyError = new Tx_Extbase_Validation_PropertyError('uploadData');
			$propertyError->addErrors(array(
					new Tx_Extbase_Validation_Error('There was a problem with uploadData',$errorCode)
			));
			$this->errors[] = $propertyError;
		}

		return $valid;
	}

}
?>