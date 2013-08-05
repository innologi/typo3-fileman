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
 * Links Validator
 *
 * Used to check if $file::$link contains 1 valid url per row
 *
 * Do not confuse with a validator aimed at the 'Link' domain model
 *
 * @package fileman
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Tx_Fileman_Domain_Validator_LinksValidator extends Tx_Extbase_Validation_Validator_AbstractValidator {

	/**
	 * Validate links, one per row
	 *
	 * @param	string	$links	The links to validate
	 * @return	boolean
	 */
	public function isValid($links) { #@TODO don't forget TCA
		$linkArray = array();

		if (isset($links[0])) {
			$links = str_replace("\r\n","\n",$links); #@SHOULD get this from a transient getter, which probably requires us to put this in the File Validator
			$linkArray = t3lib_div::trimExplode("\n", $links,1);
		}

		if (!empty($linkArray)) {
			foreach ($linkArray as $link) {
				//each link needs to be a valid URL or errors ensue
				if (!t3lib_div::isValidUrl($link)) {
					$extName = 'Fileman';
					$errorMessage = Tx_Extbase_Utility_Localization::translate('tx_fileman_validator.error_links', $extName);
					$this->addError($errorMessage, time()); #@TODO fix time()
					return FALSE;
				}
			}
		}

		//either the field is empty, or the links are all okay
		return TRUE;
	}
}
?>