<?php
namespace Innologi\Fileman\ViewHelpers\Order;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2016-2019 Frenck Lutke <typo3@innologi.nl>, www.innologi.nl
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
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;
use TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
/**
 * Order by Hierarchy Viewhelper
 *
 * @package fileman
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class HierarchyViewHelper extends AbstractViewHelper {
	use CompileWithRenderStatic;

	/**
	 * @var array
	 */
	protected static $processed = [];

	/**
	 * Initialize all arguments.
	 *
	 * @return void
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('recursionProperty', 'string', 'Property to base recursion of ordering on. Usually refers to an ObjectStorage.', TRUE);
		$this->registerArgument('labelProperty', 'string', 'Label property that is to be adjusted to display an item\'s hierarchical position.', TRUE);
		$this->registerArgument('noDuplicates', 'boolean', 'If TRUE, prevents the processing of duplicate items (e.g. a subitem to multiple items).', FALSE, FALSE);
		$this->registerArgument('collection', 'array', 'Collection to sort', TRUE);
	}

	/**
	 * @param array $arguments
	 * @param \Closure $renderChildrenClosure
	 * @param RenderingContextInterface $renderingContext
	 * @return string
	 */
	public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext) {
		$newCollection = [];
		foreach ($arguments['collection'] as $i => $item) {
			static::recursion($arguments, $newCollection, $item, (string)$i);
		}
		return $newCollection;
	}

	/**
	 * Recursion method
	 *
	 * @param array $arguments
	 * @param array &$collection
	 * @param \TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject $item
	 * @param string $index
	 * @param string $preLabel
	 * @return void
	 */
	protected static function recursion(array $arguments, array &$collection, AbstractDomainObject $item, $index, $preLabel = '| ') {
		$collection[$index] = $item;
		/** @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage $subItems */
		$subItems = $item->_getProperty($arguments['recursionProperty']);

		if ($subItems->valid()) {
			$preSort = array();
			foreach ($subItems as $key => $subItem) {
				$label = NULL;
				if (isset(static::$processed[$key])) {
					if ($arguments['noDuplicates']) {
						// Preventing a previously processed subitem (e.g. because he's subitem of multiple items)
						// helps prevent give them erroneous labels. Consider that the resulting collection offered
						// to e.g. a select element will eliminate any double values anyway.
						continue;
					}
					// original label
					$label = static::$processed[$key];
				}
				/** @var \TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject $subItem */
				if ($label === NULL) {
					$label = $subItem->_getProperty($arguments['labelProperty']);
				}
				$preSort[$label] = $subItem;
				$subItem->_setProperty($arguments['labelProperty'], $preLabel . $label);
				static::$processed[$key] = $label;
			}
			ksort($preSort);

			$preLabel .= '| ';
			foreach ($preSort as $i => $subItem) {
				$i = $index . '_' . $i;
				static::recursion($arguments, $collection, $subItem, $i, $preLabel);
			}
		}
	}

}