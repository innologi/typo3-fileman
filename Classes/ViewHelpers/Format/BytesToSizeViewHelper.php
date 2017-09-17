<?php
namespace Innologi\Fileman\ViewHelpers\Format;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2016-2017 Frenck Lutke <typo3@innologi.nl>, www.innologi.nl
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
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;
/**
 * Bytes To Size View Helper
 *
 * @package fileman
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class BytesToSizeViewHelper extends AbstractViewHelper {
	use CompileWithRenderStatic;

	/**
	 * Initialize all arguments.
	 *
	 * @return void
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('labels', 'string', 'Labels for different sizes', FALSE, ' | Kb| MB| GB');
		$this->registerArgument('bytes', 'integer', 'Bytes to format', TRUE);
	}

	/**
	 * @param array $arguments
	 * @param \Closure $renderChildrenClosure
	 * @param RenderingContextInterface $renderingContext
	 * @return string
	 */
	public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext) {
		return GeneralUtility::formatSize($arguments['bytes'], $arguments['labels']);
	}

}
