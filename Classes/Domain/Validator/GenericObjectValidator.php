<?php
namespace Innologi\Fileman\Domain\Validator;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2017 Frenck Lutke <typo3@innologi.nl>, www.innologi.nl
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
 * GenericObjectValidator override
 *
 * Fixes the 8.7.5 issue where Error results are reset because a child property of the same object
 * as the parent object receives the same instance of GenericObjectValidator
 *
 * Note that the linked issue has it the wrong way around. For param1.param2, it is param1 which loses
 * all its previous validation results once param2 replaces $this->result upon validate(). Also, its
 * proposed solution is nowhere near needed. The required fix is extremely simple.
 * @see https://forge.typo3.org/issues/77338
 *
 * @package fileman
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class GenericObjectValidator extends \TYPO3\CMS\Extbase\Validation\Validator\GenericObjectValidator {

	/**
	 * Checks if the given value is valid according to the validator, and
	 * returns the Error Messages object which occurred.
	 *
	 * @param mixed $value The value that should be validated
	 * @return \TYPO3\CMS\Extbase\Error\Result
	 * @api
	 */
	public function validate($value) {
		if (version_compare(TYPO3_branch, '8.7', '>')) {
			// @TODO review on TYPO3 v9
			return parent::validate($value);
		}

		$originalResult = $this->result;
		$newResult = parent::validate($value);
		if ($originalResult === NULL) {
			return $this->result;
		}
		$this->result = $originalResult;
		return $newResult;
	}
}
