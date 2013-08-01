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
 *
 *
 * @package fileman
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Tx_Fileman_Domain_Validator_FileValidator extends Tx_Extbase_Validation_Validator_AbstractValidator {
	#@TODO doc
	#@TODO delete failed files?
	/**
	 * @param mixed $file Is usually empty, the real value is in FILES
	 * @return	boolean
	 */
	public function isValid($file) {
		$valid = FALSE;
		$extName = 'Fileman';

		if ($file instanceof Tx_Fileman_Domain_Model_File) {
			//in case of edit action
			if ($file->getFileUri() !== NULL) {
				$valid = TRUE;
			} else {

				//file upload parameters
				$e = 'tx_fileman_filelist'; //ext_plugin name
				$s = 'files'; //storage name
				$i = 'file'; //instance name
				$p = 'fileUri'; //property name

				$uploadTmpName = array_shift($_FILES[$e]['tmp_name'][$s][$i]);
				$uploadTmpName = $uploadTmpName[$p];

				if (!isset($uploadTmpName[0]) || !file_exists($uploadTmpName)) {
					//there was no file uploaded or something went wrong
					$errorMessage = Tx_Extbase_Utility_Localization::translate('tx_fileman_validator.error_file_uri', $extName);
					$propertyError = new Tx_Extbase_Validation_PropertyError('fileUri');
					$propertyError->addErrors(array(
							new Tx_Extbase_Validation_Error($errorMessage, time())
					)); #@TODO time()? fix it
					$this->errors[] = $propertyError;
				} else {
					$uploadName = array_shift($_FILES[$e]['name'][$s][$i]);
					$uploadName = $uploadName[$p];

					$file->setTmpFile($uploadTmpName);
					$file->setFileUri($uploadName);

					$title = $file->getAlternateTitle();
					if (empty($title)) {
						$file->setAlternateTitle($uploadName);
					}
					$valid = TRUE;
				}
			}
		} else {
			#@TODO error
		}

		return $valid;
	}
}
?>