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
 * Object Properties Validator, validates an object based on its properties' validation.
 *
 * @package fileman
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Tx_Fileman_Domain_Validator_ObjectPropertiesValidator extends Tx_Fileman_Validation_Validator_PreppedAbstractValidator {

	/**
	 * Checks if an object is valid according to all its properties by passing
	 * the object through the BaseValidatorConjunction from the Extbase
	 * Validator Resolver.
	 *
	 * If at least one error occurred, the result is FALSE and all property-errors will
	 * be merged with $this->errors.
	 *
	 * @param mixed $value The value that should be validated
	 * @return boolean TRUE if the value is valid, FALSE if an error occured
	 */
	public function isValid($value) {
		if (!is_object($value)) { //also works on objectStorage objects
			#@TODO error?
		}

		$validatorResolver = $this->objectManager->get('Tx_Extbase_Validation_ValidatorResolver');
		$validator = $validatorResolver->getBaseValidatorConjunction(get_class($value));
		if ($validator->isValid($value)) {
			return TRUE;
		}

		$this->errors = array_merge($this->errors,$validator->getErrors());
		return FALSE;
	}

}
?>