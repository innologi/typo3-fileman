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
	 * frontendUserRepository
	 *
	 * @var Tx_Fileman_Domain_Repository_FrontendUserRepository
	 */
	protected $frontendUserRepository;

	/**
	 * frontendUserGroupRepository
	 *
	 * @var Tx_Fileman_Domain_Repository_FrontendUserGroupRepository
	 */
	protected $frontendUserGroupRepository;

	/**
	 * injectFrontendUserRepository
	 *
	 * @param Tx_Fileman_Domain_Repository_FrontendUserRepository $frontendUserRepository
	 * @return void
	 */
	public function injectFrontendUserRepository(Tx_Fileman_Domain_Repository_FrontendUserRepository $frontendUserRepository) {
		$this->frontendUserRepository = $frontendUserRepository;
	}

	/**
	 * injectFrontendUserGroupRepository
	 *
	 * @param Tx_Fileman_Domain_Repository_FrontendUserGroupRepository $frontendUserGroupRepository
	 * @return void
	 */
	public function injectFrontendUserGroupRepository(Tx_Fileman_Domain_Repository_FrontendUserGroupRepository $frontendUserGroupRepository) {
		$this->frontendUserGroupRepository = $frontendUserGroupRepository;
	}

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
	 * action list
	 *
	 * @param Tx_Fileman_Domain_Model_Category $category The category to show files of
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

		$suGroup = $this->frontendUserGroupRepository->findByUid($this->settings['suGroup']);
		$this->view->assign('suGroup', $suGroup);
	}

	/**
	 * action download
	 *
	 * @param Tx_Fileman_Domain_Model_File $file
	 * @param boolean $no_cache
	 * @return void
	 */
	public function downloadAction(Tx_Fileman_Domain_Model_File $file, $no_cache = FALSE) {
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
	 * @param Tx_Fileman_Domain_Model_Category $category
	 * @param Tx_Fileman_Domain_Model_File $file
	 * @dontvalidate $file
	 * @return void
	 */
	public function newAction(Tx_Fileman_Domain_Model_Category $category, Tx_Fileman_Domain_Model_File $file = NULL) {
		$this->view->assign('category', $category);
		$this->view->assign('file', $file);
	}

	/**
	 * action create
	 *
	 * @param $file
	 * @param $category
	 * @return void
	 */
	public function createAction(Tx_Fileman_Domain_Model_File $file, Tx_Fileman_Domain_Model_Category $category) {
		//correct all file upload parameters
		$e = 'tx_fileman_filelist'; //ext_plugin name
		$i = 'file'; //instance name
		$p = 'fileUri'; //property name
		$fileName = $_FILES[$e]['name'][$i][$p];
		$tmpPath = $_FILES[$e]['tmp_name'][$i][$p];

		$fileFunctions = t3lib_div::makeInstance('t3lib_basicFileFunctions');
		#$absDirPath = PATH_site.$this->settings['uploadDir'];
		$absDirPath = PATH_site.'uploads/tx_fileman/'; #@SHOULD might as well do it static right now
		//check/create dirpath
		if ($this->_check_and_create_dir($absDirPath)) {
			$finalPath = $fileFunctions->getUniqueName($fileName, $absDirPath);
			t3lib_div::upload_copy_move($tmpPath, $finalPath);
			$file->setFileUri(basename($finalPath)); #@TODO godver de godver de godver, TCA group verwacht hier de filename, niet het pad! dus voor nu aangepast

			//category
			$arguments = NULL;
			if ($category !== NULL) {
				$category->addFile($file); //this is to make the database field counter update reliably
				$this->categoryRepository->update($category); //necessary from 6.1 and upwards
				$file->addCategory($category);
				$arguments = array('category'=>$category);
			}

			//feUser
			global $TSFE;
			if ($TSFE->fe_user) {
				$feUser = $this->frontendUserRepository->findByUid($TSFE->fe_user->user['uid']);
				$file->setFeUser($feUser);
			}

			//finalize creation
			$this->fileRepository->add($file);
			$flashMessage = Tx_Extbase_Utility_Localization::translate('tx_fileman_filelist.new_file_success', $this->extensionName);
			$this->flashMessageContainer->add($flashMessage);
		} else {
			#@TODO throw exception
			//directory does not exist and could not be created
		}
		$this->redirect('list',NULL,NULL,$arguments);
	}

	/**
	 * action edit
	 *
	 * @param Tx_Fileman_Domain_Model_Category $category
	 * @param Tx_Fileman_Domain_Model_File $file
	 * @return void
	 */
	public function editAction(Tx_Fileman_Domain_Model_Category $category, Tx_Fileman_Domain_Model_File $file) {
		$this->view->assign('category', $category);
		$this->view->assign('file', $file);
	}

	/**
	 * action update
	 *
	 * @param Tx_Fileman_Domain_Model_Category $category
	 * @param Tx_Fileman_Domain_Model_File $file
	 * @return void
	 */
	public function updateAction(Tx_Fileman_Domain_Model_Category $category, Tx_Fileman_Domain_Model_File $file) {
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
	 * @param Tx_Fileman_Domain_Model_Category $category
	 * @param Tx_Fileman_Domain_Model_File $file
	 * @return void
	 */
	public function deleteAction(Tx_Fileman_Domain_Model_Category $category, Tx_Fileman_Domain_Model_File $file) {
		$this->fileRepository->remove($file);
		$flashMessage = Tx_Extbase_Utility_Localization::translate('tx_fileman_filelist.delete_file_success', $this->extensionName);
		$this->flashMessageContainer->add($flashMessage);

		//category
		$arguments = NULL;
		if ($category !== NULL) {
			$arguments = array('category'=>$category);
		}

		$this->redirect('list',NULL,NULL,$arguments);
	}

	/**
	 * A template method for displaying custom error flash messages, or to
	 * display no flash message at all on errors. Override this to customize
	 * the flash message in your action controller.
	 *
	 * @return string|boolean The flash message or FALSE if no flash message should be set
	 */
	protected function getErrorFlashMessage() {
		return FALSE;
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

	/**
	 * Checks if a directory exists. If it doesn't, it attempts to create it one directory at a time.
	 *
	 * @param	string		$dirpath	The path to the directory
	 * @return	boolean		True on success, false on failure
	 */
	private function _check_and_create_dir($dirpath) {
		//split the dirpath for use by mkdir_deep
		$matches = array();
		$pattern = '=^(([a-z]:)?/)(.*)$=i'; //.. thus windows-paths are assumed to have been corrected!
		preg_match($pattern,$dirpath,$matches);
		//if dir doesn't exist, mkdir_deep creates every nonexisting directory from its second argument..
		if (!is_dir($dirpath) && !is_null(t3lib_div::mkdir_deep($matches[1],$matches[3]))) {
			//mkdir_deep only returns something on errors
			return false;
		}
		return true;
	}

}
?>