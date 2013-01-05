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
class Tx_Fileman_ViewHelpers_IsEmptyViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractConditionViewHelper {

	/**
	 * fileRepository
	 *
	 * @var Tx_Fileman_Domain_Repository_FileRepository
	 */
	protected $fileRepository;

	/**
	 * linkRepository
	 *
	 * @var Tx_Fileman_Domain_Repository_LinkRepository
	 */
	protected $linkRepository;

	/**
	 * injectFileRepository
	 *
	 * @param Tx_Fileman_Domain_Repository_FileRepository $fileRepository
	 * @return void
	 */
	public function injectFileRepository(Tx_Fileman_Domain_Repository_FileRepository $fileRepository) {
		$this->fileRepository = $fileRepository;
	}

	/**
	 * injectLinkRepository
	 *
	 * @param Tx_Fileman_Domain_Repository_LinkRepository $linkRepository
	 * @return void
	 */
	public function injectLinkRepository(Tx_Fileman_Domain_Repository_LinkRepository $linkRepository) {
		$this->linkRepository = $linkRepository;
	}

	/**
	 * @param string $category Category
	 * @return string the rendered string
	 * @see t3lib_div::camelCaseToLowerCaseUnderscored(),t3lib_div::underscoredToLowerCamelCase(),t3lib_div::underscoredToUpperCamelCase()
	 */
	public function render($category) {
		$files = $this->fileRepository->findAllByCategory($category)->toArray();
		$links = $this->linkRepository->findAllByCategory($category)->toArray();

		if (empty($files) && empty($links)) {
			return $this->renderThenChild();
		} else {
			return $this->renderElseChild();
		}
	}

}
?>