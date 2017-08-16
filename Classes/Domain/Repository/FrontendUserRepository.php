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

/**
 * FontendUser repository. Used to ditch the record_type requirement of the original model.
 *
 * @package fileman
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Tx_Fileman_Domain_Repository_FrontendUserRepository extends Tx_Fileman_Persistence_NoPersistRepository {

	/**
	 * Finds possible owners of categories
	 *
	 * @param integer $userGroup
	 * @param Tx_Fileman_Domain_Model_FrontendUser $currentUser
	 * @param Tx_Fileman_Domain_Model_FrontendUser $currentOwner
	 * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
	 */
	public function findPossibleOwners($userGroup = 0, Tx_Fileman_Domain_Model_FrontendUser $currentUser = NULL, Tx_Fileman_Domain_Model_FrontendUser $currentOwner = NULL) {
		$query = $this->createQuery();
		$query->getQuerySettings()->setRespectStoragePage(FALSE);

		$conditions = array();
		if ($userGroup > 0) {
			$conditions[] = $query->contains('usergroup', $userGroup);
		}
		if ($currentUser !== NULL) {
			$conditions[] = $query->equals('uid', $currentUser->getUid());
		}
		if ($currentOwner !== NULL) {
			$conditions[] = $query->equals('uid', $currentOwner->getUid());
		}
		if (!empty($conditions)) {
			$query->matching(
				$query->logicalOr($conditions)
			);
		}

		return $query->execute();
	}

}
