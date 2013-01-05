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
class Tx_Fileman_Controller_LinkController extends Tx_Extbase_MVC_Controller_ActionController {

	/**
	 * linkRepository
	 *
	 * @var Tx_Fileman_Domain_Repository_LinkRepository
	 */
	protected $linkRepository;

	/**
	 * frontendUserRepository
	 *
	 * @var Tx_Extbase_Domain_Repository_FrontendUserRepository
	 */
	protected $frontendUserRepository;

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
	 * injectFrontendUserRepository
	 *
	 * @param Tx_Extbase_Domain_Repository_FrontendUserRepository $frontendUserRepository
	 * @return void
	 */
	public function injectFrontendUserRepository(Tx_Extbase_Domain_Repository_FrontendUserRepository $frontendUserRepository) {
		$this->frontendUserRepository = $frontendUserRepository;
	}

	/**
	 * action new
	 *
	 * @param Tx_Fileman_Domain_Model_Category $category
	 * @param Tx_Fileman_Domain_Model_Link $link
	 * @dontvalidate $link
	 * @return void
	 */
	public function newAction( Tx_Fileman_Domain_Model_Category $category, Tx_Fileman_Domain_Model_Link $link = NULL) {
		$this->view->assign('category', $category);
		$this->view->assign('link', $link);
	}

	/**
	 * action create
	 *
	 * @param Tx_Fileman_Domain_Model_Category $category
	 * @param Tx_Fileman_Domain_Model_Link $link
	 * @return void
	 */
	public function createAction(Tx_Fileman_Domain_Model_Category $category, Tx_Fileman_Domain_Model_Link $link) {
		global $TSFE;
		if ($TSFE->fe_user) {
			$feUser = $this->frontendUserRepository->findByUid($TSFE->fe_user->user['uid']);
			$link->setFeUser($feUser);
		}

		//category
		$arguments = NULL;
		if ($category !== NULL) {
			$link->addCategory($category);
			$arguments = array('category'=>$category);
		}

		$this->linkRepository->add($link);
		$flashMessage = Tx_Extbase_Utility_Localization::translate('tx_fileman_filelist.new_link_success', $this->extensionName);
		$this->flashMessageContainer->add($flashMessage);
		$this->redirect('list','File',NULL,$arguments);
	}

	/**
	 * action edit
	 *
	 * @param Tx_Fileman_Domain_Model_Category $category
	 * @param Tx_Fileman_Domain_Model_Link $link
	 * @return void
	 */
	public function editAction(Tx_Fileman_Domain_Model_Category $category, Tx_Fileman_Domain_Model_Link $link) {
		$this->view->assign('category', $category);
		$this->view->assign('link', $link);
	}

	/**
	 * action update
	 *
	 * @param Tx_Fileman_Domain_Model_Category $category
	 * @param Tx_Fileman_Domain_Model_Link $link
	 * @return void
	 */
	public function updateAction(Tx_Fileman_Domain_Model_Category $category, Tx_Fileman_Domain_Model_Link $link) {
		$this->linkRepository->update($link);
		$flashMessage = Tx_Extbase_Utility_Localization::translate('tx_fileman_filelist.edit_link_success', $this->extensionName);
		$this->flashMessageContainer->add($flashMessage);

		//category
		$arguments = NULL;
		if ($category !== NULL) {
			$arguments = array('category'=>$category);
		}

		$this->redirect('list','File',NULL,$arguments);
	}

	/**
	 * action delete
	 *
	 * @param Tx_Fileman_Domain_Model_Category $category
	 * @param Tx_Fileman_Domain_Model_Link $link
	 * @return void
	 */
	public function deleteAction(Tx_Fileman_Domain_Model_Category $category, Tx_Fileman_Domain_Model_Link $link) {
		$this->linkRepository->remove($link);
		$flashMessage = Tx_Extbase_Utility_Localization::translate('tx_fileman_filelist.delete_link_success', $this->extensionName);
		$this->flashMessageContainer->add($flashMessage);

		//category
		$arguments = NULL;
		if ($category !== NULL) {
			$arguments = array('category'=>$category);
		}

		$this->redirect('list','File',NULL,$arguments);
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

}
?>