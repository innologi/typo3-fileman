<?php
namespace Innologi\Fileman\Domain\Validator;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012-2017 Frenck Lutke <typo3@innologi.nl>, www.innologi.nl
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
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;
use TYPO3\CMS\Extbase\Validation\Error;
/**
 * Handles optional domain validations for the File domain.
 *
 * In fact, these are required for our use-cases, but we call it optional
 * in the context of Extbase not forcing it where it doesn't suit us.
 *
 * @package fileman
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class OptionalFileValidator extends AbstractValidator {

	/**
	 * @var \TYPO3\CMS\Extbase\Validation\ValidatorResolver
	 * @inject
	 */
	protected $validatorResolver;

	/**
	 * File service
	 *
	 * @var \Innologi\Fileman\Service\FileService
	 * @inject
	 */
	protected $fileService;

	/**
	 * Necessary to resolve $settings
	 *
	 * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManager
	 */
	protected $configurationManager;

	/**
	 * TypoScript settings
	 *
	 * @var array
	 */
	protected $settings = [];

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
	 * Does specific file domain validation.
	 *
	 * Note that we merge errors in the order the fields are displayed,
	 * otherwise the order seems counterintuitive.
	 *
	 * @param \Innologi\Fileman\Domain\Model\File $file
	 * @return void
	 */
	public function isValid($file) {
		$validateCategory = FALSE;

		// check if an actual file upload took place
		if ($this->fileService->next()) {
			// note @ fileUri: NULL @ first-time create, NOT NULL after validation error / js upload

			$errorCode = 0;
			//if fileUri is not NULL, fileService->isValid() will fail because the file isn't uploaded "again"
			if ($file->getFileUri() === NULL && !$this->fileService->isValid()) {
				//there was no file uploaded or something went wrong
				$errorCode = 40750133702;
			} elseif (!$this->fileService->isAllowed(
				$this->settings['allowFileType'],
				$this->settings['denyFileType'],
				$file->getFileUri()
			)) {
				//the file type is prohibited by configuration
				$errorCode = 40750133701;
				// delete denied file
				$this->fileService->removeFile();
			}

			if ($errorCode > 0) {
				// uploadData errors might ensue after a successful upload, so clear the file name so we don't lose the upload-field
				$this->fileService->clearFileName();
				$file->setFileUri(NULL);
				//setup error message
				$this->result->forProperty('uploadData')->addError(new Error('There was a problem with uploadData', $errorCode));
			} else {
				//assign uploadfile attributes to the $file entry
				$this->fileService->setFileProperties($file);
			}

		} elseif ($file->getFileUri() === NULL) {
			// if no file upload and no fileUri value, then what kind of trickery is this!?
			$this->addError('No file uploaded nor any previously uploaded file found.', 1505486144);
		} else {
			// getting here means we're doing an edit, in which case we want to validate one more property.
				// we don't want it at all in new-action. Alternatively we could now add category through the
				// new-action form, which wasn't an option in 4.5. But at least this way we're consistent.
			$validateCategory = TRUE;
		}

		//validate all the fields we'd normally use @validate annotations for
		$title = $file->getAlternateTitle();
		$textValidator = $this->validatorResolver->createValidator('Text');
		$result = $textValidator->validate($title);
		if ($result->hasMessages()) {
			$this->result->forProperty('alternateTitle')->merge($result);
		}
		$result = $textValidator->validate($file->getDescription());
		if ($result->hasMessages()) {
			$this->result->forProperty('description')->merge($result);
		}
		$result = $this->validatorResolver->createValidator(LinksValidator::class)->validate($file->getLinks());
		if ($result->hasMessages()) {
			$this->result->forProperty('links')->merge($result);
		}
		if ($validateCategory) {
			$result = $this->validatorResolver->createValidator('NotEmpty')->validate($file->getCategory());
			if ($result->hasMessages()) {
				$this->result->forProperty('category')->merge($result);
			}
		}

		// if no errors
		if (!$this->result->hasMessages()) {
			//replace empty title
			if (empty($title)) {
				$file->setAlternateTitle($file->getFileUri());
			}
		}
	}

}