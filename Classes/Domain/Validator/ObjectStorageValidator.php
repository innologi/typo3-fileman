<?php
namespace Innologi\Fileman\Domain\Validator;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Frenck Lutke <typo3@innologi.nl>, www.innologi.nl
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
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Validation\ValidatorResolver;
use Innologi\Fileman\Validation\Validator\PreppedAbstractValidator;
use Innologi\Fileman\Validation\StorageError;
use Innologi\Fileman\Domain\Model\File;
/**
 * Object Storage Validator, validates an ObjectStorage's objects.
 *
 * @package fileman
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class ObjectStorageValidator extends PreppedAbstractValidator {

	/**
	 * File service
	 *
	 * @var \Innologi\Fileman\Service\FileService
	 * @inject
	 */
	protected $fileService;

	/**
	 * Checks if an object is a valid objectstorage by passing all its objects
	 * through the objectsPropertiesValidator. Options are passed to the
	 * objectsPropertiesValidator.
	 *
	 * Utilizes a StorageError to help us differentiate @ form.error viewhelper.
	 *
	 * If at least one error occurred, the result is FALSE and all property-errors will
	 * be merged with $this->errors.
	 *
	 * @param mixed $value The value that should be validated
	 * @return boolean TRUE if the value is valid, FALSE if an error occured
	 */
	public function isValid($value) {
		#if ($this->configurationManager->isFeatureEnabled('rewrittenPropertyMapper')) {
			#@TODO finish this for TYPO3 6.3+, as well as in other validators and VHs
		#	$this->result->merge($validator->validate());
		#} else {
			$valid = FALSE;
			$storageError = NULL;

			if ($value instanceof ObjectStorage) {
				$validator = $this->objectManager->get(ValidatorResolver::class)->createValidator(ObjectPropertiesValidator::class, $this->options);
				$valid = TRUE;

				//if the storage is a File ObjectStorage, we need to initialize some stuff for fileService that need to happen exactly once per storage
				if ($value->current() instanceof File) {
					$this->fileService->findSubstitutes();
					$this->fileService->reset();
				} //we could really only remove this if we create a FileStorage validator from which the objectStorage validation originates, and place it there instead

				foreach ($value as $obj) {
					if (!$validator->isValid($obj)) {
						$valid = FALSE;

						if (!isset($storageError)) {
							$propertyName = str_replace('Innologi\\Fileman\\Domain\\Model\\','',get_class($obj));
							$propertyName[0] = strtolower($propertyName[0]);
							$storageError = new StorageError($propertyName);
						}

						$storageError->addErrors($obj->getIndex(), $validator->getErrors());
					}
				}
				if (!$valid) {
					$this->errors[] = $storageError;
				}
			}
			return $valid;
		#}
	}

}