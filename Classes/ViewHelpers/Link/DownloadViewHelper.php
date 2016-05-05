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
 * Download View Helper
 *
 * @package fileman
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Tx_Fileman_ViewHelpers_Link_DownloadViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractTagBasedViewHelper {

	/**
	 * @var string
	 */
	protected $tagName = 'a';

	/**
	 *
	 * @param string $fileUri The filepath
	 * @param string $title File description
 	 * @param string $noDocrootAction Target action in case filepath lies outside docroot
	 * @param array $noDocrootArguments Arguments in case filepath lies outside docroot
	 * @param string $noDocrootController Target controller in case filepath lies outside docroot. If NULL current controllerName is used
	 * @param integer $pageUid Target page
	 * @return string
	 */
	public function render($fileUri, $title = NULL, $noDocrootAction = NULL, array $noDocrootArguments = NULL, $noDocrootController = NULL, $pageUid = NULL) {
		//if the fileUri lies within docroot, this will resolve the valid sitepath to file, otherwise: boolean false
		$validSitepath = $this->_resolveSitepath($fileUri);

		if ($validSitepath) {
			$this->tag->addAttribute('href', $validSitepath);
			#$this->tag->addAttribute('title', $title);
		} else {
			//since the file isn't accessible from docroot, we need to feed the file through a specialized download action
			$uriBuilder = $this->controllerContext->getUriBuilder();
			//after a quick look @ Tx_Fluid_ViewHelpers_Link_ActionViewHelper..
			$uri = $uriBuilder->reset()
				->setTargetPageUid($pageUid)
				->setUseCacheHash(FALSE)
				->setCreateAbsoluteUri(TRUE)
				->uriFor($noDocrootAction, $noDocrootArguments, $noDocrootController);

			$this->tag->addAttribute('href', $uri);
		}

		$this->tag->setContent($this->renderChildren());
		$this->tag->forceClosingTag(TRUE);
		return $this->tag->render();
	}

	/**
	 * Returns the documentroot-relative path of the file, if the file lies within.
	*
	* @param	string		$filepath	The absolute filepath
	* @return	mixed		String with relative path or boolean false on failure
	*/
	private function _resolveSitepath($filepath) {
		$siteUrl = t3lib_div::getIndpEnv('TYPO3_SITE_URL');

		//filepath is relative to document root
		#$appendUrl = $siteUrl.$filepath;
		$appendUrl = $siteURL.'uploads/tx_fileman/'.$filepath; #@LOW might as well do it static right now
		if (is_file($appendUrl)) {
			return $appendUrl;
		}

		//filepath is already a valid sitepath
		if (strpos($filepath,$siteUrl) === 0) {
			return $filepath;
		}

		//if not yet a valid sitepath, check if filepath lies within document root
		$docroot = t3lib_div::getIndpEnv('TYPO3_DOCUMENT_ROOT') . '/';
		//in case TYPO3 DOCUMENT ROOT fails (i.e. old FE in BE situations)
		if (!isset($docroot[1])) {
			$docroot = $_SERVER['DOCUMENT_ROOT'];
			if (isset($docroot[0])) {
				$docroot = t3lib_div::fixWindowsFilePath($docroot);
				$docroot_rev = strrev($docroot);
				if ($docroot_rev[0] !== '/') {
					$docroot .= '/';
				}
			} else {
				//could not retrieve document root
				#@TODO throw exception
				return FALSE;
			}
		}

		//returns the sitepath only if the filepath lies within document root
		if (strpos($filepath,$docroot) === 0) {
			return $siteUrl . str_replace($docroot,'',$filepath);
		}

		//could not resolve sitepath to filepath
		return FALSE;
	}

}

?>