<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012-2016 Frenck Lutke <frenck@innologi.nl>, www.innologi.nl
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
 * Category repository
 *
 * @package fileman
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Tx_Fileman_Domain_Repository_CategoryRepository extends Tx_Extbase_Persistence_Repository {

	/**
	 * Returns all objects of this repository that are in the root (no parents)
	 *
	 * @return array An array of objects, empty if no objects found
	 */
	public function findInRoot() {
		$query = $this->createQuery();
		$result = $query->matching(
			$query->equals('parentCategory', 0)
		)->execute();
		return $result;
	}

	/**
	 * Returns all objects of this repository belonging to the provided category
	 *
	 * @param Tx_Fileman_Domain_Model_Category $category The category to show subcategories of
	 * @return array An array of objects, empty if no objects found
	 */
	public function findAllByParentCategory(Tx_Fileman_Domain_Model_Category $category) {
		$query = $this->createQuery();
		$result = $query->matching(
			$query->contains('parentCategory', $category)
		)->execute();
		return $result;
	}

	/**
	 * Returns all objects with feUser set
	 *
	 * @param Tx_Fileman_Domain_Model_FrontendUser $feUser
	 * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
	 */
	public function findByFeUser(Tx_Fileman_Domain_Model_FrontendUser $feUser) {
		$query = $this->createQuery();
		return $query->matching(
			$query->equals('feUser', $feUser)
		)->execute();
	}

}