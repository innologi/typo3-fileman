<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013-2016 Frenck Lutke <typo3@innologi.nl>, www.innologi.nl
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
use TYPO3\CMS\Core\SingletonInterface;
/**
 * Facilitates user/group control.
 *
 * @package fileman
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Tx_Fileman_Service_UserService implements SingletonInterface {

	/**
	 * Logged in frontend user
	 *
	 * @var Tx_Fileman_Domain_Model_FrontendUser
	 */
	protected $feUser = NULL;

	/**
	 * Array containing boolean values mapped to group ID keys
	 *
	 * @var array
	 */
	protected $inGroup = array();

	/**
	 * frontendUserRepository
	 *
	 * @var Tx_Fileman_Domain_Repository_FrontendUserRepository
	 * @inject
	 */
	protected $frontendUserRepository;

	/**
	 * frontendUserGroupRepository
	 *
	 * @var Tx_Fileman_Domain_Repository_FrontendUserGroupRepository
	 * @inject
	 */
	protected $frontendUserGroupRepository;

	/**
	 * Returns current frontend user.
	 *
	 * @return Tx_Fileman_Domain_Model_FrontendUser
	 */
	public function getCurrentUser() {
		if ($this->feUser === NULL) {
			global $TSFE;
			$feUser = FALSE;
			if (isset($TSFE->fe_user->user['uid'])) {
				$returnVal = $this->frontendUserRepository->findByUid($TSFE->fe_user->user['uid']);
				if ($returnVal) {
					$feUser = $returnVal;
				}
			}
			$this->feUser = $feUser;
		}
		return $this->feUser;
	}

	/**
	 * Returns whether the user is part of the usergroup.
	 *
	 * @param integer $groupId Usergroup ID
	 * @return boolean
	 */
	public function isInGroup($groupId) {
		if (!isset($this->inGroup[$groupId])) {
			$inGroup = FALSE;
			if ($groupId) {
				$feUser = $this->getCurrentUser();
				if ($feUser) {
					$group = $this->frontendUserGroupRepository->findByUid($groupId);
					if ($group && $feUser->getUsergroup()->contains($group)) {
						$inGroup = TRUE;
					}
				}
			}
			$this->inGroup[$groupId] = $inGroup;
		}
		return $this->inGroup[$groupId];
	}

	/**
	 * Returns whether the user is the category admin
	 *
	 * @param Tx_Fileman_Domain_Model_Category $category
	 * @return boolean
	 */
	public function isCategoryAdmin(Tx_Fileman_Domain_Model_Category $category = NULL) {
		$isAdmin = FALSE;
		if ($category !== NULL) {
			$owner = $category->getFeUser();
			$isAdmin = $owner !== NULL && $this->feUser !== NULL && $owner->getUid() === $this->feUser->getUid();
		}
		return $isAdmin;
	}

	/**
	 * Returns session data under key
	 *
	 * @param string $key
	 * @return mixed|NULL
	 */
	public function getSessionData($key) {
		/* @var \TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication $frontendAuth */
		$frontendAuth = $GLOBALS['TSFE']->fe_user;
		return $frontendAuth->getSessionData($key);
	}

	/**
	 * Saves session data under key
	 *
	 * @param string $key
	 * @param string $data
	 * @return void
	 */
	public function putSessionData($key, $data) {
		/* @var \TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication $frontendAuth */
		$frontendAuth = $GLOBALS['TSFE']->fe_user;
		$frontendAuth->setAndSaveSessionData($key, $data);
	}

}
