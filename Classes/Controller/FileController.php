<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012-2013 Frenck Lutke <frenck@innologi.nl>, www.innologi.nl
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
 * File controller
 *
 * @package fileman
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Tx_Fileman_Controller_FileController extends Tx_Fileman_MVC_Controller_ActionController {

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
	 * File service
	 *
	 * @var Tx_Fileman_Service_FileService
	 */
	protected $fileService;

	/**
	 * Injects the File Service
	 *
	 * @param Tx_Fileman_Service_FileService $fileService
	 * @return void
	 */
	public function injectFileService(Tx_Fileman_Service_FileService $fileService) {
		$this->fileService = $fileService;
	}

	/**
	 * injectFileRepository
	 *
	 * @param Tx_Fileman_Domain_Repository_FileRepository $fileRepository
	 * @return void
	 */
	public function injectFileRepository(Tx_Fileman_Domain_Repository_FileRepository $fileRepository) {
		$this->fileRepository = $fileRepository;
		$fileRepository->setDefaultOrderings(array(
				'alternateTitle' => Tx_Extbase_Persistence_QueryInterface::ORDER_ASCENDING
		));
	}

	/**
	 * injectLinkRepository
	 *
	 * @param Tx_Fileman_Domain_Repository_LinkRepository $linkRepository
	 * @return void
	 */
	public function injectLinkRepository(Tx_Fileman_Domain_Repository_LinkRepository $linkRepository) {
		$this->linkRepository = $linkRepository;
		$linkRepository->setDefaultOrderings(array(
				'linkName' => Tx_Extbase_Persistence_QueryInterface::ORDER_ASCENDING
		));
	}



	/**
	 * action list
	 *
	 * Also shows links
	 *
	 * @param Tx_Fileman_Domain_Model_Category $category The category to show files of
	 * @dontvalidate $category
	 * @return void
	 */
	public function listAction(Tx_Fileman_Domain_Model_Category $category = NULL) {
		if ($category === NULL) {
			$files = $this->fileRepository->findAll();
			$links = $this->linkRepository->findAll();
		} else {
			$files = $this->fileRepository->findAllByCategory($category);
			$links = $this->linkRepository->findAllByCategory($category);
			$this->view->assign('category', $category);
		}

		$this->view->assign('files', $files);
		$this->view->assign('links', $links);

		if ($this->feUser) {
			$isSuperUser = $this->userService->isInGroup(intval($this->settings['suGroup']));
			$this->view->assign('isSuperUser', $isSuperUser);
			$this->view->assign('isLoggedIn', TRUE);
		}
	}

	/**
	 * action download
	 *
	 * @param Tx_Fileman_Domain_Model_File $file
	 * @param boolean $no_cache
	 * @return void
	 */
	public function downloadAction(Tx_Fileman_Domain_Model_File $file, $no_cache = FALSE) { #@SHOULD currently unused
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
			t3lib_div::cleanOutputBuffers();
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
	 * @param Tx_Fileman_Domain_Model_Category $category
	 * @param Tx_Fileman_Domain_Model_FileStorage $files
	 * @dontvalidate $category
	 * @dontvalidate $files
	 * @dontverifyrequesthash
	 * @return void
	 */
	public function newAction(Tx_Fileman_Domain_Model_Category $category, Tx_Fileman_Domain_Model_FileStorage $files = NULL) {
		if ($files !== NULL) {
			//when a validation error occurs, $files will contain all file entries, but the attributes given
			//by fileService during validation, because the forward() tells extbase to re-map the request arguments
			$this->fileService->reset(); //so start over!
			$fileStorage = $files->getFile();
			foreach ($fileStorage as $hash=>$file) {
				if ($this->fileService->next()) {
					//each uploaded file that was validated, is associated with the matching $file entry
					//this would obviously not work if their count and order wasn't identical
					$this->fileService->setFileProperties($file);
				} else {
					#@TODO error
					unset($fileStorage[$hash]);
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
	 * @param Tx_Fileman_Domain_Model_FileStorage $files
	 * @param Tx_Fileman_Domain_Model_Category $category
	 * @dontvalidate $category
	 * @dontverifyrequesthash
	 * @return void
	 */
	public function createAction(Tx_Fileman_Domain_Model_FileStorage $files, Tx_Fileman_Domain_Model_Category $category) {
		$fileStorage = $files->getFile();
		foreach ($fileStorage as $file) {
			#$absDirPath = PATH_site.$this->settings['uploadDir'];
			$absDirPath = PATH_site.'uploads/tx_fileman/'; #@SHOULD might as well do it static right now
			//moves a file from it's tmp location to it final destination
			if ($this->fileService->finalizeMove($file,$absDirPath)) {
				//feUser
				$file->setFeUser($this->feUser);
				#@TODO do the alternate title stuff here
				//category
				if ($category !== NULL) {
					$category->addFile($file); //this is to make the database field counter update reliably
					$file->addCategory($category);
				}

				//finalize creation
				$this->fileRepository->add($file);
			} else {
				#@TODO error
				//move could not take place
				//unlink?
			}
		}

		$flashMessage = Tx_Extbase_Utility_Localization::translate('tx_fileman_filelist.new_file_success', $this->extensionName);
		$this->flashMessageContainer->add($flashMessage);

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
	 * @param Tx_Fileman_Domain_Model_Category $category
	 * @param Tx_Fileman_Domain_Model_File $file
	 * @dontvalidate $category
	 * @dontvalidate $file
	 * @return void
	 */
	public function editAction(Tx_Fileman_Domain_Model_Category $category, Tx_Fileman_Domain_Model_File $file) {
		$this->view->assign('category', $category); //category is given for URL-consistency and redirecting afterwards
		$this->view->assign('file', $file);
	}

	/**
	 * action update
	 *
	 * Note the file/files difference with create action
	 *
	 * @param Tx_Fileman_Domain_Model_Category $category
	 * @param Tx_Fileman_Domain_Model_File $file
	 * @dontvalidate $category
	 * @return void
	 */
	public function updateAction(Tx_Fileman_Domain_Model_Category $category, Tx_Fileman_Domain_Model_File $file) {
		//empty titles are replaced
		$title = $file->getAlternateTitle();
		if (empty($title)) {
			$file->setAlternateTitle($file->getFileUri());
		}

		$this->fileRepository->update($file);
		$flashMessage = Tx_Extbase_Utility_Localization::translate('tx_fileman_filelist.edit_file_success', $this->extensionName);
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
	 * @param Tx_Fileman_Domain_Model_Category $category
	 * @param Tx_Fileman_Domain_Model_File $file
	 * @dontvalidate $category
	 * @dontvalidate $file
	 * @return void
	 */
	public function deleteAction(Tx_Fileman_Domain_Model_Category $category, Tx_Fileman_Domain_Model_File $file) {
		$this->fileRepository->remove($file);
		$flashMessage = Tx_Extbase_Utility_Localization::translate('tx_fileman_filelist.delete_file_success', $this->extensionName);
		$this->flashMessageContainer->add($flashMessage);

		//category
		$arguments = NULL;
		if ($category !== NULL) {
			$category->removeFile($file);
			$arguments = array('category'=>$category);
		}
		#@FIXME delete file?

		$this->redirect('list',NULL,NULL,$arguments);
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
		return Tx_Extbase_Utility_Localization::translate('tx_fileman_filelist.error_message', $this->extensionName);
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