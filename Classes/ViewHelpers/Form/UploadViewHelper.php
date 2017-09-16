<?php
namespace Innologi\Fileman\ViewHelpers\Form;
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
 * Form.Upload ViewHelper
 *
 * Adds support for the required attribute
 *
 * @package fileman
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class UploadViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Form\UploadViewHelper {

	/**
	 * Initialize the arguments.
	 *
	 * @return void
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('required', 'bool', 'If the field is required or not', FALSE, FALSE);
	}

	/**
	 * Renders the upload field.
	 *
	 * @return string @api
	 */
	public function render() {
		if ($this->arguments['required'] !== FALSE) {
			$this->tag->addAttribute('required', 'required');
		}
		return parent::render();
	}
}