<?php
namespace Innologi\Fileman\ViewHelpers\Link;
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
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Innologi\Fileman\Domain\Model\File;
use TYPO3\CMS\Extbase\Mvc\Exception\InvalidArgumentTypeException;
/**
 * Download View Helper
 *
 * @package fileman
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class DownloadViewHelper extends AbstractTagBasedViewHelper {

	/**
	 * @var string
	 */
	protected $tagName = 'a';

	/**
	 * {@inheritDoc}
	 * @see \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper::initializeArguments()
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerTagAttribute('title', 'string', 'Title');
		$this->registerArgument('file', 'object', 'File', TRUE);
		$this->registerArgument('noDocrootAction', 'string', 'Controller action name, in case of downloads outside of docroot.', FALSE, 'download');
		$this->registerArgument('noDocrootArguments', 'array', 'Controller action arguments in addition to \'file\', in case of downloads outside of docroot.', FALSE, []);
		$this->registerArgument('noDocrootController', 'string', 'Controller name, in case of downloads outside of docroot.', FALSE, 'File');

	}

	/**
	 * Renders download link.
	 *
	 * @return string
	 */
	public function render() {
		$file = $this->arguments['file'];
		if (!$file instanceof File) {
			throw new InvalidArgumentTypeException(
				'DownloadViewHelper expects \'file\' attribute to be of class ' . File::class . ', instead got ' . get_class($file),
				1505665616
			);
		}

		//if the fileUri lies within docroot, this will resolve the valid sitepath to file, otherwise: boolean false
		$validSitepath = $this->resolveSitepath($file->getFileUri());

		if ($validSitepath) {
			$this->tag->addAttribute('href', $validSitepath);
		} else {
			// @TODO this is likely not to work in T3v8, looking at the FALSE useCacheHash
				// either way, this part isn't used as long as we only allow uploads via FAL to public locations
				// so it's not worth checking until we add the private-file feature back in

			//since the file isn't accessible from docroot, we need to feed the file through a specialized download action
			$uriBuilder = $this->controllerContext->getUriBuilder();
			$uri = $uriBuilder->reset()
				->setUseCacheHash(FALSE)
				->setCreateAbsoluteUri(TRUE)
				->uriFor(
					$this->arguments['noDocrootAction'],
					array_merge($this->arguments['noDocrootArguments'], ['file' => $file]),
					$this->arguments['noDocrootController']
				);
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
	protected function resolveSitepath($filepath) {
		//filepath is relative to document root
		$appendUrl = 'uploads/tx_fileman/' . $filepath; #@LOW might as well do it static right now
		if (is_file($appendUrl)) {
			return $appendUrl;
		}

		//filepath is already a valid sitepath
		$siteUrl = GeneralUtility::getIndpEnv('TYPO3_SITE_URL');
		if (strpos($filepath, $siteUrl) === 0) {
			return $filepath;
		}

		//if not yet a valid sitepath, check if filepath lies within document root
		$docroot = GeneralUtility::getIndpEnv('TYPO3_DOCUMENT_ROOT') . '/';
		//in case TYPO3 DOCUMENT ROOT fails (i.e. old FE in BE situations)
		if (!isset($docroot[1])) {
			$docroot = $_SERVER['DOCUMENT_ROOT'];
			if (isset($docroot[0])) {
				$docroot = rtrim(GeneralUtility::fixWindowsFilePath($docroot), '/') . '/';
			} else {
				//could not retrieve document root
				#@TODO throw exception
				return FALSE;
			}
		}

		//returns the sitepath only if the filepath lies within document root
		if (strpos($filepath, $docroot) === 0) {
			return $siteUrl . str_replace($docroot, '', $filepath);
		}

		//could not resolve sitepath to filepath
		return FALSE;
	}

}