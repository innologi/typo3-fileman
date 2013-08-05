<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Frenck Lutke <frenck@innologi.nl>, www.innologi.nl
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
 * LinkUri validator
 *
 * @package fileman
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Tx_Fileman_Domain_Validator_LinkUriValidator extends Tx_Extbase_Validation_Validator_AbstractValidator {

	/**
	 * Checks if $link is a valid URL
	 *
	 * @param	string	$link	The link to validate
	 * @return	boolean
	 */
	public function isValid($link) {
		if (!isset($link[0]) || !is_string($link) || !t3lib_div::isValidUrl($link)) {
			$extName = 'Fileman';
			$errorMessage = Tx_Extbase_Utility_Localization::translate('tx_fileman_validator.error_link_uri', $extName);
			$this->addError($errorMessage, time()); #@TODO fix time()
			return FALSE;
		}

		//link is okay
		return TRUE;
	}
}
?>