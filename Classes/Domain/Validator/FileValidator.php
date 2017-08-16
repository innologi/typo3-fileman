<?php
namespace Innologi\Fileman\Domain\Validator;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012-2016 Frenck Lutke <typo3@innologi.nl>, www.innologi.nl
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
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
/**
 * Handles additional domain validations for the File domain.
 *
 * @package fileman
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class FileValidator extends AbstractValidator {
	// @TODO this file is a travesty, please please PLEASE refactor.
	/**
	 * TypoScript settings
	 *
	 * @var array
	 */
	protected $settings;

	/**
	 * Necessary to resolve $settings
	 *
	 * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManager
	 */
	protected $configurationManager;

	/**
	 * File service
	 *
	 * @var \Innologi\Fileman\Service\FileService
	 * @inject
	 */
	protected $fileService;

	/**
	 * Injects the Configuration manager and initializes $settings
	 *
	 * @param \TYPO3\CMS\Extbase\Configuration\ConfigurationManager $configurationManager
	 * @return void
	 */
	public function injectConfigurationManager(ConfigurationManager $configurationManager) {
		$this->configurationManager = $configurationManager;
		$this->settings = $this->configurationManager->getConfiguration(ConfigurationManager::CONFIGURATION_TYPE_SETTINGS);
	}

	/**
	 * Does some specific file domain validation.
	 *
	 * @param mixed $file
	 * @return	boolean
	 */
	public function isValid($file) {
		$valid = FALSE;
		#$extName = 'Fileman';
		$this->errors = array();
		#$errorMessage = '';
		$errorCode = 0;

		//only proceed if instance matches and an actual file upload took place
		if ($file instanceof \Innologi\Fileman\Domain\Model\File) {
			if ($this->fileService->next()) {
				$file->setIndex($this->fileService->getIndex()); //we need this regardless, to bind errors from this and other validators to the right file
				// note @ fileUri: NULL @ first-time create, NOT NULL after validation error / js upload
				if (!$this->fileService->isAllowed(
					$this->settings['allowFileType'],
					$this->settings['denyFileType'],
					$file->getFileUri()
				)) {
					//the file type is prohibited by configuration
					$errorCode = 40750133701;
					// delete denied file
					$this->fileService->removeFile();
					//if fileUri is not NULL, fileService->isValid() will fail because the file isn't uploaded "again"
				} elseif ($file->getFileUri() === NULL && !$this->fileService->isValid()) {
					//there was no file uploaded or something went wrong
					$errorCode = 40750133702;
				} else {
					$valid = TRUE;
				}
			} elseif ($file->getFileUri() !== NULL) {
				if ($file->getCategory()->count() > 0) {
					//edit action
					$valid = TRUE;
				} else {
					$propertyError = new Tx_Extbase_Validation_PropertyError('category');
					$propertyError->addErrors(array(
						new Tx_Extbase_Validation_Error('There was a problem with category', 40750133705)
					));
					$this->errors[] = $propertyError;
					return FALSE;
				}
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
			$file->setFileUri(NULL);
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