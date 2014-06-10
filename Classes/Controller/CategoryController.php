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
 * Category controller
 *
 * @package fileman
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Tx_Fileman_Controller_CategoryController extends Tx_Fileman_MVC_Controller_ActionController {

	/**
	 * action list
	 *
	 * @return void
	 */
	public function listAction() {
		$categories = $this->categoryRepository->findInRoot();
		$this->view->assign('categories', $categories);

		if ($this->feUser) {
			$isSuperUser = $this->userService->isInGroup(intval($this->settings['suGroup']));
			$this->view->assign('isSuperUser', $isSuperUser);
			$this->view->assign('isLoggedIn', TRUE);
		}
	}

	/**
	 * action new
	 *
	 * @param $category
	 * @param $parentCategory
	 * @dontvalidate $category
	 * @ignorevalidation $category
	 * @dontvalidate $parentCategory
	 * @ignorevalidation $parentCategory
	 * @return void
	 */
	public function newAction(Tx_Fileman_Domain_Model_Category $category = NULL, Tx_Fileman_Domain_Model_Category $parentCategory = NULL) {
		$this->view->assign('category', $category);
		$this->view->assign('parentCategory', $parentCategory);
	}

	/**
	 * action create
	 *
	 * @param Tx_Fileman_Domain_Model_Category $category
	 * @param Tx_Fileman_Domain_Model_Category $parentCategory
	 * @dontvalidate $parentCategory
	 * @ignorevalidation $parentCategory
	 * @return void
	 */
	public function createAction(Tx_Fileman_Domain_Model_Category $category, Tx_Fileman_Domain_Model_Category $parentCategory = NULL) {
		$category->setFeUser($this->feUser);
		if ($parentCategory !== NULL) {
			$category->addParentCategory($parentCategory);
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
	 * @dontvalidate $category
	 * @ignorevalidation $category
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
	 * @dontvalidate $category
	 * @ignorevalidation $category
	 * @return void
	 */
	public function deleteAction(Tx_Fileman_Domain_Model_Category $category) {
		$this->categoryRepository->remove($category);
		$flashMessage = Tx_Extbase_Utility_Localization::translate('tx_fileman_filelist.delete_category_success', $this->extensionName);
		$this->flashMessageContainer->add($flashMessage);
		$this->redirect('list');
	}

}
?>