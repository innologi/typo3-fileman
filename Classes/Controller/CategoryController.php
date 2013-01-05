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
class Tx_Fileman_Controller_CategoryController extends Tx_Extbase_MVC_Controller_ActionController {

	/**
	 * categoryRepository
	 *
	 * @var Tx_Fileman_Domain_Repository_CategoryRepository
	 */
	protected $categoryRepository;

	/**
	 * frontendUserRepository
	 *
	 * @var Tx_Extbase_Domain_Repository_FrontendUserRepository
	 */
	protected $frontendUserRepository;

	/**
	 * frontendUserGroupRepository
	 *
	 * @var Tx_Extbase_Domain_Repository_FrontendUserGroupRepository
	 */
	protected $frontendUserGroupRepository;

	/**
	 * injectCategoryRepository
	 *
	 * @param Tx_Fileman_Domain_Repository_CategoryRepository $categoryRepository
	 * @return void
	 */
	public function injectCategoryRepository(Tx_Fileman_Domain_Repository_CategoryRepository $categoryRepository) {
		$this->categoryRepository = $categoryRepository;
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
	 * injectFrontendUserGroupRepository
	 *
	 * @param Tx_Extbase_Domain_Repository_FrontendUserGroupRepository $frontendUserGroupRepository
	 * @return void
	 */
	public function injectFrontendUserGroupRepository(Tx_Extbase_Domain_Repository_FrontendUserGroupRepository $frontendUserGroupRepository) {
		$this->frontendUserGroupRepository = $frontendUserGroupRepository;
	}

	/**
	 * action list
	 *
	 * @return void
	 */
	public function listAction() {
		$categories = $this->categoryRepository->findAll();
		$this->view->assign('categories', $categories);
		$suGroup = $this->frontendUserGroupRepository->findByUid($this->settings['suGroup']);
		$this->view->assign('suGroup', $suGroup);
	}

	/**
	 * action new
	 *
	 * @param $category
	 * @dontvalidate $category
	 * @return void
	 */
	public function newAction(Tx_Fileman_Domain_Model_Category $category = NULL) {
		$this->view->assign('category', $category);
	}

	/**
	 * action create
	 *
	 * @param $category
	 * @return void
	 */
	public function createAction(Tx_Fileman_Domain_Model_Category $category) {
		global $TSFE;
		if ($TSFE->fe_user) {
			$feUser = $this->frontendUserRepository->findByUid($TSFE->fe_user->user['uid']);
			$category->setFeUser($feUser);
		}
		$this->categoryRepository->add($category);
		$flashMessage = Tx_Extbase_Utility_Localization::translate('tx_fileman_filelist.new_category_success', $this->extensionName);
		$this->flashMessageContainer->add($flashMessage);
		$this->redirect('list');
	}

	/**
	 * action edit
	 *
	 * @param Tx_Fileman_Domain_Model_Category $category
	 * @return void
	 */
	public function editAction(Tx_Fileman_Domain_Model_Category $category) {
		$this->view->assign('category', $category);
	}

	/**
	 * action update
	 *
	 * @param Tx_Fileman_Domain_Model_Category $category
	 * @return void
	 */
	public function updateAction(Tx_Fileman_Domain_Model_Category $category) {
		$this->categoryRepository->update($category);
		$flashMessage = Tx_Extbase_Utility_Localization::translate('tx_fileman_filelist.edit_category_success', $this->extensionName);
		$this->flashMessageContainer->add($flashMessage);
		$this->redirect('list');
	}

	/**
	 * action delete
	 *
	 * @param Tx_Fileman_Domain_Model_Category $category
	 * @return void
	 */
	public function deleteAction(Tx_Fileman_Domain_Model_Category $category) {
		$this->categoryRepository->remove($category);
		$flashMessage = Tx_Extbase_Utility_Localization::translate('tx_fileman_filelist.delete_category_success', $this->extensionName);
		$this->flashMessageContainer->add($flashMessage);
		$this->redirect('list');
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