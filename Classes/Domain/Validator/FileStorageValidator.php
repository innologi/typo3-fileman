<?php
namespace Innologi\Fileman\Domain\Validator;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2017-2019 Frenck Lutke <typo3@innologi.nl>, www.innologi.nl
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
/**
 * FileStorage validator
 *
 * @package fileman
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class FileStorageValidator extends AbstractValidator {

	/**
	 * @var \TYPO3\CMS\Extbase\Validation\ValidatorResolver
	 */
	protected $validatorResolver;

	/**
	 * File service
	 *
	 * @var \Innologi\Fileman\Service\FileService
	 */
	protected $fileService;

	/**
	 *
	 * @param \TYPO3\CMS\Extbase\Validation\ValidatorResolver $validatorResolver
	 * @return void
	 */
	public function injectValidatorResolver(\TYPO3\CMS\Extbase\Validation\ValidatorResolver $validatorResolver)
	{
	    $this->validatorResolver = $validatorResolver;
	}

	/**
	 *
	 * @param \Innologi\Fileman\Service\FileService $fileService
	 * @return void
	 */
	public function injectFileService(\Innologi\Fileman\Service\FileService $fileService)
	{
	    $this->fileService = $fileService;
	}

	/**
	 * Validates FileStorage object
	 *
	 * @param $fileStorage \Innologi\Fileman\Domain\Model\FileStorage
	 * @return void
	 */
	public function isValid($fileStorage) {
		// when working with fileStorage, uploads may have been performed without using $_FILES,
		// this will set the variables so we can keep our code consistent in all cases
		$this->fileService->findSubstitutes();
		$this->fileService->reset();
		$files = $fileStorage->getFile();

		// validate every single file
		$validator = $this->validatorResolver->createValidator(OptionalFileValidator::class);
		foreach ($files as $file) {
			$result = $validator->validate($file);
			if ($result->hasMessages()) {
				// on errors, we need our own indexes (which is why we can't let Extbase do this automatically)
				// @LOW it's possible that getting the index from fileService makes storing it in $file unnecessary
				$this->result->forProperty('file.' . $this->fileService->getIndex())->merge($result);
			}
		}
	}
}