<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2016 Frenck Lutke <frenck@innologi.nl>, www.innologi.nl
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
use TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject;
/**
 * Order by Hierarchy Viewhelper
 *
 * @package fileman
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Tx_Fileman_ViewHelpers_Order_HierarchyViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractViewHelper {

	/**
	 * Class constructor
	 *
	 * @return void
	 */
	public function __construct() {
		$this->registerArgument('recursionProperty', 'string', '', TRUE);
		$this->registerArgument('labelProperty', 'string', '', TRUE);
	}

	/**
	 * Orders collection by hierarchy.
	 *
	 * @param array $collection
	 * @return array
	 */
	public function render($collection) {
		$newCollection = array();

		foreach ($collection as $i => $item) {
			$this->recursion($newCollection, $item, (string)$i);
		}

		return $newCollection;
	}

	/**
	 * Recursion method
	 *
	 * @param array $collection
	 * @param \TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject $item
	 * @param string $index
	 * @param string $preLabel
	 */
	protected function recursion(array &$collection, AbstractDomainObject $item, $index, $preLabel = '| ') {
		$collection[$index] = $item;
		$subItems = $item->_getProperty($this->arguments['recursionProperty']);

		if (!empty($subItems)) {
			$preSort = array();
			foreach ($subItems as $subItem) {
				/** @var \TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject $subItem */
				$label = $subItem->_getProperty($this->arguments['labelProperty']);
				$preSort[$label] = $subItem;
				$subItem->_setProperty($this->arguments['labelProperty'], $preLabel . $label);
			}
			ksort($preSort);

			$preLabel .= '| ';
			foreach ($preSort as $i => $subItem) {
				$i = $index . '_' . $i;
				$this->recursion($collection, $subItem, $i, $preLabel);
			}
		}
	}

}