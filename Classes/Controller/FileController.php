<?php
namespace Innologi\Fileman\Controller;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012-2013 Frenck Lutke <typo3@innologi.nl>, www.innologi.nl
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
use Innologi\Fileman\MVC\Controller\ActionController;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use Innologi\Fileman\Service\SortRepositoryService;
use Innologi\Fileman\Domain\Model\Category;
use Innologi\Fileman\Domain\Model\File;
use Innologi\Fileman\Domain\Model\FileStorage;
/**
 * File controller
 *
 * @package fileman
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class FileController extends ActionController {

	// search constants
	const SEARCH_CATEGORIES = 0;
	const SEARCH_FILES = 1;
	const SEARCH_LINKS = 2;

	/**
	 * fileRepository
	 *
	 * @var \Innologi\Fileman\Domain\Repository\FileRepository
	 */
	protected $fileRepository;

	/**
	 * linkRepository
	 *
	 * @var \Innologi\Fileman\Domain\Repository\LinkRepository
	 */
	protected $linkRepository;

	/**
	 * File service
	 *
	 * @var \Innologi\Fileman\Service\FileService
	 * @inject
	 */
	protected $fileService;

	/**
	 * injectFileRepository
	 *
	 * @param \Innologi\Fileman\Domain\Repository\FileRepository $fileRepository
	 * @return void
	 */
	public function injectFileRepository(\Innologi\Fileman\Domain\Repository\FileRepository $fileRepository) {
		$this->fileRepository = $fileRepository;
		$this->sortRepositoryService->registerSortableRepository($fileRepository, [
			SortRepositoryService::SORT_FIELD_TITLE => 'alternateTitle'
		]);
	}

	/**
	 * injectLinkRepository
	 *
	 * @param \Innologi\Fileman\Domain\Repository\LinkRepository $linkRepository
	 * @return void
	 */
	public function injectLinkRepository(\Innologi\Fileman\Domain\Repository\LinkRepository $linkRepository) {
		$this->linkRepository = $linkRepository;
		$this->sortRepositoryService->registerSortableRepository($linkRepository, [
			SortRepositoryService::SORT_FIELD_TITLE => 'linkName'
		]);
	}


	/**
	 * Initializes create action
	 *
	 * Mainly propertymapping configuration for rewritten property mapper.
	 * This is preliminary, the rewrittenPropertyMapper is explicitly disabled
	 * in this extension for now.
	 *
	 * @return void
	 */
	public function initializeCreateAction() {
		if ($this->configurationManager->isFeatureEnabled('rewrittenPropertyMapper')) {
			if ($this->request->hasArgument('files')) {
				$value = $this->request->getArgument('files');
				// i2 - iN
				$indexArray = preg_grep('/^i([2-9]|([1-9][0-9]*))$/', array_keys($value['file']));
				$propertyMapConfig = $this->arguments->getArgument('files')->getPropertyMappingConfiguration();
				if (empty($indexArray)) {
					$propertyMapConfig->setTargetTypeForSubProperty('file.i1.uploadData', 'array');
				} else {
					$subPropertyMapConfig = $propertyMapConfig->getConfigurationFor('file');
					foreach ($indexArray as $index) {
						// $propertyMapConfig->allowAllProperties();
						$propertyMapConfig->allowCreationForSubProperty('file.' . $index);
						$propertyMapConfig->setTargetTypeForSubProperty('file.' . $index . '.uploadData', 'array');
						$propertyMapConfig->setTargetTypeForSubProperty('file.' . $index . '.fileUri', 'string');
						$propertyMapConfig->setTargetTypeForSubProperty('file.' . $index . '.alternateTitle', 'string');
						$propertyMapConfig->setTargetTypeForSubProperty('file.' . $index . '.description', 'string');
						$propertyMapConfig->setTargetTypeForSubProperty('file.' . $index . '.links', 'string');
						$subPropertyMapConfig->allowProperties($index);
						$subPropertyMapConfig->forProperty($index)->allowProperties('uploadData', 'alternateTitle', 'description', 'links');
					}
				}
			}
		}
	}

	/**
	 * action list
	 *
	 * Also shows links
	 *
	 * @param \Innologi\Fileman\Domain\Model\Category $category The category to show files of
	 * @dontvalidate $category
	 * @ignorevalidation $category
	 * @return void
	 */
	public function listAction(Category $category = NULL) {
		if ($category === NULL) {
			$subCategories = $this->categoryRepository->findAll();
			$files = $this->fileRepository->findAll();
			$links = $this->linkRepository->findAll();
		} else {
			$subCategories = $this->categoryRepository->findAllByParentCategory($category);
			$files = $this->fileRepository->findAllByCategory($category);
			$links = $this->linkRepository->findAllByCategory($category);
			$this->view->assign('category', $category);
		}

		// normally I would use category.subCategory, but I can't seem to sort it automatically
		// on title due to its MM table sorting, so I'm using the cleanest code I can think of
		$this->view->assign('subCategories', $subCategories);
		$this->view->assign('files', $files);
		$this->view->assign('links', $links);

		if ($this->feUser) {
			$isSuperUser = $this->userService->isInGroup(intval($this->settings['suGroup'])) || $this->userService->isCategoryAdmin($category);
			$this->view->assign('isSuperUser', $isSuperUser);
			$this->view->assign('isLoggedIn', TRUE);
		}
	}

	/**
	 * action download
	 *
	 * @param \Innologi\Fileman\Domain\Model\File $file
	 * @param boolean $no_cache
	 * @return void
	 */
	public function downloadAction(File $file, $no_cache = FALSE) { #@LOW currently unused
		$fileUri = $file->getFileUri();

		if(is_file($fileUri)) {
			//define necessary data
			$fileLen = filesize($fileUri);
			$fileName = basename($fileUri);
			$contentType = 'application/octet-stream'; //general downloadable type

			//file transfer headers
			$headers = array(
				'Content-Description'		=> 'File Transfer', //description of action
				'Content-Type'				=> $contentType, //providing the right content type will tell the user-agent what to do
				'Content-Disposition'		=> 'attachment; filename="' . $fileName . '"', //provide download dialog info, the quotes take care of spaces
				'Content-Transfer-Encoding'	=> 'binary', //inform user-agent raw binary data is being transferred unencoded
				'Accept-Ranges'				=> 'bytes', //allow bandwith optimization through byte-serving
				'Content-Length'			=> $fileLen, //allows progress indication
				'Set-Cookie'				=> NULL //cookies provide unnecessary overhead at downloads
			);

			if ($no_cache) {
				//no caching headers (for frequent updates)
				$headers['Expires']	= 'Thu, 01 Jan 1970 00:00:00 GMT'; //'Expires: 0' does NOT always produce expected results
				$headers['Cache-Control'] = 'no-cache, must-revalidate';
				//losing these enforces use of Expires by user-agents that prefer these or don't play nice with cache-control (e.g. HTTP/1.0)
				$headers['Last-Modified'] = NULL;
				$headers['ETag'] = NULL;
			}

			foreach($headers as $header => $data) {
				$this->response->setHeader($header, $data, TRUE);
			}

			//in an extbase situation, we're deep in its outputbuffers, which could (and often WILL) corrupt the download
			while ($content = ob_get_clean()) {
				$obContent .= $content;
			}
			$this->response->sendHeaders(); //send headers after cleaning OB
			//before reading the file, we need to purge everything in our buffers towards user-agent
			ob_flush();
			flush();

			//utilize a chunked-readfile due to possible configuration-specific buffersize problems
			if (!$this->_readfile_by_chunks($fileUri)) {
				//.. unless it fails
				readfile($fileUri);
			}

			//make sure the download is the ONLY thing done; end all script-processing
			exit;

		} else {
			#@TODO throw exception
			$this->redirect('list','Category');
		}
	}

	/**
	 * action new
	 *
	 * Requires @dontverifyrequesthash because of the forward when a validation error occurs @ create action.
	 *
	 * @param \Innologi\Fileman\Domain\Model\Category $category
	 * @param \Innologi\Fileman\Domain\Model\FileStorage $files
	 * @dontvalidate $category
	 * @ignorevalidation $category
	 * @dontvalidate $files
	 * @ignorevalidation $files
	 * @dontverifyrequesthash
	 * @return void
	 */
	public function newAction(Category $category, FileStorage $files = NULL) {
		if ($files !== NULL) {
			//when a validation error occurs, $files will contain all file entries, but not the attributes given
			//by fileService during validation, because forward() tells extbase to re-map the request arguments
			$this->fileService->reset(); //so start over!
			$fileStorage = $files->getFile();
			/** @var File $file */
			foreach ($fileStorage as $hash=>$file) {
				if ($this->fileService->next()) {
					//each uploaded file that was validated, is associated with the matching $file entry
					//this would obviously not work if their count and order wasn't identical
					$this->fileService->setFileProperties($file);
				} else {
					if (version_compare(TYPO3_branch, '6.2', '<')) {
						unset($fileStorage[$hash]);
					} else {
						unset($fileStorage[$file]);
					}
					$this->addFlashMessage(
						LocalizationUtility::translate('tx_fileman_filelist.new_file_failed_reconstitute', $this->extensionName, array($file->getFileUri())),
						'',
						FlashMessage::WARNING
					);
				}
			}
		}

		$this->view->assign('category', $category);
		$this->view->assign('files', $files);
	}

	/**
	 * action create
	 *
	 * Because the new action allows us to alter the form, we have to issue a @dontverifyrequesthash here.
	 *
	 * @param \Innologi\Fileman\Domain\Model\FileStorage $files
	 * @param \Innologi\Fileman\Domain\Model\Category $category
	 * @dontvalidate $category
	 * @ignorevalidation $category
	 * @dontverifyrequesthash
	 * @verifycsrftoken
	 * @return void
	 */
	public function createAction(FileStorage $files, Category $category) {
		$fileStorage = $files->getFile();
		$failedFiles = array();
		/** @var File $file */
		foreach ($fileStorage as $file) {
			#$absDirPath = PATH_site.$this->settings['uploadDir'];
			$absDirPath = PATH_site.'uploads/tx_fileman/'; #@LOW might as well do it static right now
			//moves a file from it's tmp location to it final destination
			if ($this->fileService->finalizeMove($file,$absDirPath)) {
				//feUser
				$file->setFeUser($this->feUser);
				#@TODO do the alternate title stuff here
				//category
				if ($category !== NULL) {
					$category->addFile($file); //this is to make the database field counter update reliably
					$file->addCategory($category);
					$file->setFeGroup($category->getFeGroup());
				}

				//finalize creation
				$this->fileRepository->add($file);
			} else {
				$failedFiles[] = $file;
			}
		}

		if (empty($failedFiles)) {
			$flashMessage = LocalizationUtility::translate('tx_fileman_filelist.new_file_success', $this->extensionName);
			$severity = FlashMessage::OK;
		} else {
			$flashMessage = LocalizationUtility::translate('tx_fileman_filelist.new_file_error', $this->extensionName, array(count($failedFiles)));
			$severity = FlashMessage::ERROR;
		}
		$this->addFlashMessage($flashMessage, '', $severity);

		$arguments = NULL;
		if ($category !== NULL) {
			$this->categoryRepository->update($category); //necessary from 6.1 and upwards
			$arguments = array('category'=>$category);
		}
		$this->redirect('list',NULL,NULL,$arguments);
	}

	/**
	 * action edit
	 *
	 * Note the file/files difference with new action
	 *
	 * @param \Innologi\Fileman\Domain\Model\File $file
	 * @param \Innologi\Fileman\Domain\Model\Category $category
	 * @dontvalidate $category
	 * @ignorevalidation $category
	 * @dontvalidate $file
	 * @ignorevalidation $file
	 * @return void
	 */
	public function editAction(File $file, Category $category = NULL) {
		$this->view->assign('category', $category); //category is given for URL-consistency and redirecting afterwards

		// if the user isn't a superUser, categories should be limited to those he owns
		$isSuperUser = $this->userService->isInGroup(intval($this->settings['suGroup']));
		$categories = $isSuperUser
			? $this->categoryRepository->findInRoot()
			: $this->categoryRepository->findByFeUser($this->feUser);

		$this->view->assign('categories', $categories->toArray());
		$this->view->assign('isSuperUser', $isSuperUser);
		$this->view->assign('file', $file);
	}

	/**
	 * action update
	 *
	 * Note the file/files difference with create action
	 *
	 * @param \Innologi\Fileman\Domain\Model\File $file
	 * @param \Innologi\Fileman\Domain\Model\Category $category
	 * @dontvalidate $category
	 * @ignorevalidation $category
	 * @verifycsrftoken
	 * @return void
	 */
	public function updateAction(File $file, Category $category = NULL) {
		//empty titles are replaced
		$title = $file->getAlternateTitle();
		if (empty($title)) {
			$file->setAlternateTitle($file->getFileUri());
		}

		$this->fileRepository->update($file);
		$flashMessage = LocalizationUtility::translate('tx_fileman_filelist.edit_file_success', $this->extensionName);
		$this->flashMessageContainer->add($flashMessage);

		//category
		$arguments = NULL;
		if ($category !== NULL) {
			$arguments = array('category'=>$category);
		}

		$this->redirect('list',NULL,NULL,$arguments);
	}

	/**
	 * action delete
	 *
	 * Also explicitly removes $file from $category, to make sure the counters of this bi-directional relation are in order
	 *
	 * @param \Innologi\Fileman\Domain\Model\File $file
	 * @param \Innologi\Fileman\Domain\Model\Category $category
	 * @dontvalidate $category
	 * @ignorevalidation $category
	 * @dontvalidate $file
	 * @ignorevalidation $file
	 * @verifycsrftoken
	 * @return void
	 */
	public function deleteAction(File $file, Category $category = NULL) {
		$arguments = NULL;
		$controller = 'Category';
		$fileCategories = $file->getCategory();

		//category
		if ($category !== NULL) {
			$file->removeCategory($category);
			//$category->removeFile($file);
			$arguments = array('category'=>$category);
			$controller = NULL;
		} elseif ($fileCategories->count() > 0) {
			// If we get here, it means file was attempted to be removed outside of its category, e.g. via search.
			// This action suggests the file needs to be removed ENTIRELY, regardless. So first remove it from any
			// category.
			foreach ($fileCategories as $fC) {
				$file->removeCategory($fC);
			}
		}

		// whats next depends on whether it has any remaining category
		if ($fileCategories->count() === 0) {
			$this->fileRepository->remove($file);

			#@LOW change this as soon as its no longer static / using FAL
			$uri = PATH_site . 'uploads/tx_fileman/' . $file->getFileUri();
			try {
				unlink($uri);
			} catch (\Exception $e) {
				// @LOW log?
			}

			$flashMessage = LocalizationUtility::translate('tx_fileman_filelist.delete_file_success', $this->extensionName);
		} else {
			$this->fileRepository->update($file);
			$flashMessage = LocalizationUtility::translate('tx_fileman_filelist.remove_file_success', $this->extensionName);
		}

		$this->flashMessageContainer->add($flashMessage);
		$this->redirect('list',$controller,NULL,$arguments);
	}

	/**
	 * action search
	 *
	 * @param string $search
	 * @return void
	 */
	public function searchAction($search = NULL) {
		$resultCount = 0;
		$search = $search === NULL ? '' : trim($search);

		if (isset($search[0])) {
			$searchTypes = GeneralUtility::intExplode(',', $this->settings['searchTypes']);
			$searchTerms = GeneralUtility::trimExplode(' ', $search, 1);

			if (in_array(self::SEARCH_CATEGORIES, $searchTypes)) {
				$categories = $this->categoryRepository->search($searchTerms);
				$resultCount += $categories->count();
				$this->view->assign('categories', $categories);
			}
			if (in_array(self::SEARCH_FILES, $searchTypes)) {
				$files = $this->fileRepository->search($searchTerms);
				$resultCount += $files->count();
				$this->view->assign('files', $files);
			}
			if (in_array(self::SEARCH_LINKS, $searchTypes)) {
				$links = $this->linkRepository->search($searchTerms);
				$resultCount += $links->count();
				$this->view->assign('links', $links);
			}

			// for now, it suffices to base superuser rights only on the su-group
			if ($this->feUser) {
				$isSuperUser = $this->userService->isInGroup(intval($this->settings['suGroup']));
				$this->view->assign('isSuperUser', $isSuperUser);
				$this->view->assign('isLoggedIn', TRUE);
			}
		}

		// if there are no results (or valid searchterm) ..
		$this->view->assign('noResults', $resultCount < 1);
		$this->view->assign('search', $search);
	}


	/**
	 * A template method for displaying custom error flash messages, or to
	 * display no flash message at all on errors. Override this to customize
	 * the flash message in your action controller.
	 *
	 * @return string The flash message or FALSE if no flash message should be set
	 * @api
	 */
	protected function getErrorFlashMessage() {
		return LocalizationUtility::translate('tx_fileman_filelist.error_message', $this->extensionName);
	}


	/**
	 * Variation of readfile(), to read by chunks. This variation
	 * is preferred over the original readfile(), due to buffer
	 * variations per server. Slightly altered version from the
	 * ones found in the comments in the PHP manual.
	 *
	 * @param	string		$file		The file to read
	 * @param	integer		$chunksize	The number of MB's per chunk
	 * @return	boolean		Answers whether reading the file was a success
	 * @author Various <http://www.php.net/manual/en/function.readfile.php>
	 */
	private function _readfile_by_chunks($file, $chunksize=1) {
		$chunksize = $chunksize * (1024*1024);
		if (($fp = fopen($file, 'rb')) === false) {
			return false;
		}
		while (!feof($fp)) {
			$buffer = fread($fp, $chunksize);
			echo $buffer;
			ob_flush();
			flush();
		}
		return fclose($fp);
	}

}
?>