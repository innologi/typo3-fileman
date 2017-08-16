<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Frenck Lutke <typo3@innologi.nl>, www.innologi.nl
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
 * CamelCase View Helper
 *
 * @package fileman
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Tx_Fileman_ViewHelpers_Format_CamelCaseViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractViewHelper {

	/**
	 * @param string $type Type of conversion [ camelCaseToLowerCaseUnderscored / underscoredToLowerCamelCase / underscoredToUpperCamelCase ]
	 * @param string $value The value to format
	 * @return string
	 * @see t3lib_div::camelCaseToLowerCaseUnderscored(),t3lib_div::underscoredToLowerCamelCase(),t3lib_div::underscoredToUpperCamelCase()
	 */
	public function render($type, $value = NULL) {
		if ($value === NULL) {
			$value = $this->renderChildren();
		}
		if (is_string($value)) {
			switch ($type) {
				case 'camelCaseToLowerCaseUnderscored':
				case 'underscoredToLowerCamelCase':
				case 'underscoredToUpperCamelCase':
					$value = t3lib_div::$type($value);
					break;
				default:
					break;
			}
		}

		return $value;
	}

}
?>