<?php
namespace Innologi\Fileman\Controller;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012-2019 Frenck Lutke <typo3@innologi.nl>, www.innologi.nl
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
use Innologi\Fileman\Mvc\Controller\ActionController;
use Innologi\Fileman\Domain\Model\Category;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
/**
 * Category controller
 *
 * @package fileman
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class CategoryController extends ActionController {

	/**
	 * FrontendUserRepository
	 *
	 * @var \Innologi\Fileman\Domain\Repository\FrontendUserRepository
	 */
	protected $frontendUserRepository;

	/**
	 *
	 * @param \Innologi\Fileman\Domain\Repository\FrontendUserRepository $frontendUserRepository
	 * @return void
	 */
	public function injectFrontendUserRepository(\Innologi\Fileman\Domain\Repository\FrontendUserRepository $frontendUserRepository)
	{
	    $this->frontendUserRepository = $frontendUserRepository;
	}

	/**
	 * {@inheritDoc}
	 * @see \Innologi\Fileman\Mvc\Controller\ActionController::initializeAction()
	 */
	protected function initializeAction() {
		// doing this in the appropriate initialize methods is too late, so..
		$this->disableRequireLogin(['list', 'sort']);
		parent::initializeAction();
	}

	/**
	 * Initializes create action
	 *
	 * @return void
	 */
	protected function initializeCreateAction() {
		$id = $this->request->hasArgument('parentCategory') && isset($this->request->getArgument('parentCategory')[0])
			? $this->request->getArgument('parentCategory')
			: $this->feUser->getUid();
		$this->validateRequest('stoken', $id);
	}

	/**
	 * Initializes update action
	 *
	 * @return void
	 */
	protected function initializeUpdateAction() {
		$this->validateRequest();
	}

	/**
	 * Initializes delete action
	 *
	 * @return void
	 */
	protected function initializeDeleteAction() {
		$this->validateRequest('stoken', NULL, 'category');
	}

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
	 * @extensionScannerIgnoreLine
	 * @ignorevalidation $category
	 * @extensionScannerIgnoreLine
	 * @ignorevalidation $parentCategory
	 * @TYPO3\CMS\Extbase\Annotation\IgnoreValidation("category")
	 * @TYPO3\CMS\Extbase\Annotation\IgnoreValidation("parentCategory")
	 * @return void
	 */
	public function newAction(Category $category = NULL, Category $parentCategory = NULL) {
		$this->view->assign('category', $category);
		$this->view->assign('parentCategory', $parentCategory);
		$this->view->assign('feUser', $this->feUser);
		$this->view->assign('users',
			$this->frontendUserRepository->findPossibleOwners(
				(int) $this->settings['possibleOwnerGroup'],
				$this->feUser,
				($category !== NULL ? $category->getFeUser() : NULL)
			)
		);
	}

	/**
	 * action create
	 *
	 * @param \Innologi\Fileman\Domain\Model\Category $category
	 * @param \Innologi\Fileman\Domain\Model\Category $parentCategory
	 * @extensionScannerIgnoreLine
	 * @ignorevalidation $parentCategory
	 * @TYPO3\CMS\Extbase\Annotation\IgnoreValidation("parentCategory")
	 * @return void
	 */
	public function createAction(Category $category, Category $parentCategory = NULL) {
		if ($category->getFeUser() === NULL) {
			$category->setFeUser($this->feUser);
		}
		if ($parentCategory !== NULL) {
			$category->addParentCategory($parentCategory);
			$category->setFeGroup($parentCategory->getFeGroup());
		}
		$this->categoryRepository->add($category);
		$flashMessage = LocalizationUtility::translate('tx_fileman_filelist.new_category_success', $this->extensionName);
		$this->addFlashMessage($flashMessage);

		if ($parentCategory === NULL) {
			$this->redirect('list');
		} else {
			$this->redirect('list', 'File', NULL, array('category' => $parentCategory));
		}
	}

	/**
	 * action edit
	 *
	 * @param \Innologi\Fileman\Domain\Model\Category $category
	 * @param \Innologi\Fileman\Domain\Model\Category $parentCategory
	 * @extensionScannerIgnoreLine
	 * @ignorevalidation $category
	 * @extensionScannerIgnoreLine
	 * @ignorevalidation $parentCategory
	 * @TYPO3\CMS\Extbase\Annotation\IgnoreValidation("category")
	 * @TYPO3\CMS\Extbase\Annotation\IgnoreValidation("parentCategory")
	 * @return void
	 */
	public function editAction(Category $category, Category $parentCategory = NULL) {
		$this->view->assign('category', $category);

		// if the user isn't a superUser, categories should be limited to those he owns
		$isSuperUser = $this->userService->isInGroup(intval($this->settings['suGroup']));
		$categories = $isSuperUser
			? $this->categoryRepository->findInRoot($category)
			: $this->categoryRepository->findByFeUser($this->feUser, $category);

		$this->view->assign('categories', $categories->toArray());
		$this->view->assign('parentCategory', $parentCategory);
		$this->view->assign('isSuperUser', $isSuperUser);
		$this->view->assign('users',
			$this->frontendUserRepository->findPossibleOwners(
				(int) $this->settings['possibleOwnerGroup'],
				$this->feUser,
				$category->getFeUser()
			)
		);
	}

	/**
	 * action update
	 *
	 * @param \Innologi\Fileman\Domain\Model\Category $category
	 * @param \Innologi\Fileman\Domain\Model\Category $parentCategory
	 * @extensionScannerIgnoreLine
	 * @ignorevalidation $parentCategory
	 * @TYPO3\CMS\Extbase\Annotation\IgnoreValidation("parentCategory")
	 * @return void
	 */
	public function updateAction(Category $category, Category $parentCategory = NULL) {
		if ($category->getFeUser() === NULL) {
			$category->setFeUser($this->feUser);
		}
		$this->categoryRepository->update($category);
		$flashMessage = LocalizationUtility::translate('tx_fileman_filelist.edit_category_success', $this->extensionName);
		$this->addFlashMessage($flashMessage);
		if ($parentCategory === NULL) {
			$this->redirect('list');
		} else {
			$this->redirect('list', 'File', NULL, array('category' => $parentCategory));
		}
	}

	/**
	 * action delete
	 *
	 * @param \Innologi\Fileman\Domain\Model\Category $category
	 * @param \Innologi\Fileman\Domain\Model\Category $parentCategory
	 * @extensionScannerIgnoreLine
	 * @ignorevalidation $category
	 * @extensionScannerIgnoreLine
	 * @ignorevalidation $parentCategory
	 * @TYPO3\CMS\Extbase\Annotation\IgnoreValidation("category")
	 * @TYPO3\CMS\Extbase\Annotation\IgnoreValidation("parentCategory")
	 * @return void
	 */
	public function deleteAction(Category $category, Category $parentCategory = NULL) {
		$this->categoryRepository->remove($category);
		$flashMessage = LocalizationUtility::translate('tx_fileman_filelist.delete_category_success', $this->extensionName);
		$this->addFlashMessage($flashMessage);
		if ($parentCategory === NULL) {
			$this->redirect('list');
		} else {
			$this->redirect('list', 'File', NULL, array('category' => $parentCategory));
		}
	}

}