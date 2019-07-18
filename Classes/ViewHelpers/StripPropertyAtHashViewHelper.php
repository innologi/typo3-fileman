<?php
namespace Innologi\Fileman\ViewHelpers;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2017-2019 Frenck Lutke <typo3@innologi.nl>, www.innologi.nl
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
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;
use TYPO3\CMS\Core\Utility\GeneralUtility;
/**
 * Strip Property At Hash Viewhelper
 *
 * Strips properties from ObjectStorage hashes and onward for use by validation results.
 *
 * @TODO what if we could get one step further and get the title/name of the object
 * causing the error this VH is being used on? Use-case: WHICH parentCategory is the cause?
 *
 * @package fileman
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class StripPropertyAtHashViewHelper extends AbstractViewHelper {
	use CompileWithRenderStatic;

	/**
	 * @param array $arguments
	 * @param \Closure $renderChildrenClosure
	 * @param RenderingContextInterface $renderingContext
	 * @return string
	 */
	public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext) {
		$indexFound = FALSE;
		$parts = GeneralUtility::trimExplode('.', $renderChildrenClosure(), TRUE);
		foreach ($parts as $i => $part) {
			if ($indexFound || strpos($part, '000') === 0) {
				$indexFound = TRUE;
				unset($parts[$i]);
			}
		}

		return join('.', $parts);
	}

}