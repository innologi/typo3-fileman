<?php

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
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
/**
 * Link Controller
 *
 * @package fileman
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Tx_Fileman_Controller_LinkController extends Tx_Fileman_MVC_Controller_ActionController {
	#@LOW display the links (and files) more nicely, like with file sizes, file icons, external domain mentioned, etc. This would also make it possible to eventually merge the lists

	/**
	 * linkRepository
	 *
	 * @var Tx_Fileman_Domain_Repository_LinkRepository
	 */
	protected $linkRepository;

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
	 * action new
	 *
	 * @param Tx_Fileman_Domain_Model_Category $category
	 * @param Tx_Fileman_Domain_Model_Link $link
	 * @dontvalidate $category
	 * @ignorevalidation $category
	 * @dontvalidate $link
	 * @ignorevalidation $link
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
	 * @dontvalidate $category
	 * @ignorevalidation $category
	 * @verifycsrftoken
	 * @return void
	 */
	public function createAction(Tx_Fileman_Domain_Model_Category $category, Tx_Fileman_Domain_Model_Link $link) {
		$link->setFeUser($this->feUser);

		//empty titles are replaced
		$title = $link->getLinkName();
		if (empty($title)) {
			$link->setLinkName($link->getLinkUri());
		}

		//category
		$arguments = NULL;
		if ($category !== NULL) {
			$category->addLink($link); //this is to make the database field counter update reliably
			$this->categoryRepository->update($category); //necessary from 6.1 and upwards
			$link->addCategory($category);
			$link->setFeGroup($category->getFeGroup());
			$arguments = array('category'=>$category);
		}

		$this->linkRepository->add($link);
		$flashMessage = LocalizationUtility::translate('tx_fileman_filelist.new_link_success', $this->extensionName);
		$this->flashMessageContainer->add($flashMessage);
		$this->redirect('list','File',NULL,$arguments);
	}

	/**
	 * action edit
	 *
	 * @param Tx_Fileman_Domain_Model_Category $category
	 * @param Tx_Fileman_Domain_Model_Link $link
	 * @dontvalidate $category
	 * @ignorevalidation $category
	 * @dontvalidate $link
	 * @ignorevalidation $link
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
	 * @dontvalidate $category
	 * @ignorevalidation $category
	 * @verifycsrftoken
	 * @return void
	 */
	public function updateAction(Tx_Fileman_Domain_Model_Category $category, Tx_Fileman_Domain_Model_Link $link) {
		//empty titles get replaced
		$title = $link->getLinkName();
		if (empty($title)) {
			$link->setLinkName($link->getLinkUri());
		}

		$this->linkRepository->update($link);
		$flashMessage = LocalizationUtility::translate('tx_fileman_filelist.edit_link_success', $this->extensionName);
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
	 * Also explicitly removes $link from $category, to make sure the counters of this bi-directional relation are in order
	 *
	 * @param Tx_Fileman_Domain_Model_Category $category
	 * @param Tx_Fileman_Domain_Model_Link $link
	 * @dontvalidate $category
	 * @ignorevalidation $category
	 * @dontvalidate $link
	 * @ignorevalidation $link
	 * @verifycsrftoken
	 * @return void
	 */
	public function deleteAction(Tx_Fileman_Domain_Model_Category $category, Tx_Fileman_Domain_Model_Link $link) {
		$this->linkRepository->remove($link);
		$flashMessage = LocalizationUtility::translate('tx_fileman_filelist.delete_link_success', $this->extensionName);
		$this->flashMessageContainer->add($flashMessage);

		//category
		$arguments = NULL;
		if ($category !== NULL) {
			$category->removeLink($link);
			$arguments = array('category'=>$category);
		}

		$this->redirect('list','File',NULL,$arguments);
	}

}
?>