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
 *
 *
 * @package fileman
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Tx_Fileman_Domain_Validator_FileUriValidator extends Tx_Extbase_Validation_Validator_AbstractValidator {
	#@TODO delete failed files?
	/**
	 * @param	string	$elem	Is usually empty, the real value is in FILES
	 * @return	boolean
	 */
	public function isValid($elem = NULL) {
		//in case of edit action
		if ($elem !== NULL) {
			return TRUE;
		}

		//correct all file upload parameters
		$e = 'tx_fileman_filelist'; //ext_plugin name
		$f = 'tmp_name'; //FILES field name
		$i = 'file'; //instance name
		$p = 'fileUri'; //property name

		foreach ($_FILES[$e][$f][$i][$p] as $index=>$fileUri) {
			if (!isset($fileUri[0]) || !file_exists($fileUri)) {
				unset($_FILES[$e][$f][$i][$p][$index]);
			}
		}

		if (!empty($_FILES[$e][$f][$i][$p])) {
			//a file was in fact successfully uploaded
			return TRUE;
		} else {
			//there was no file uploaded or something went wrong
			$extName = 'Fileman';
			$errorMessage = Tx_Extbase_Utility_Localization::translate('tx_fileman_validator.error_file_uri', $extName);
			$this->addError($errorMessage, time()); #@TODO time()? fix it
			return FALSE;
		}
	}
}
?>