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
 * File repository
 *
 * @package fileman
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Tx_Fileman_Domain_Repository_FileRepository extends Tx_Extbase_Persistence_Repository {

	/**
	 * Returns all objects of this repository belonging to the provided category
	 *
	 * @param Tx_Fileman_Domain_Model_Category $category The category to show files of
	 * @return array An array of objects, empty if no objects found
	 */
	public function findAllByCategory(Tx_Fileman_Domain_Model_Category $category) {
		$query = $this->createQuery();
		$result = $query->matching(
				$query->contains('category', $category)
			)->execute();
		return $result;
	}

	/**
	 * Returns all objects that match $search
	 *
	 * @param string $search
	 * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
	 */
	public function search($search) {
		$search = '%' . $search . '%';
		$query = $this->createQuery();
		return $query->matching(
			$query->logicalOr(array(
				$query->like('file_uri', $search, FALSE),
				$query->like('alternate_title', $search, FALSE),
				$query->like('description', $search, FALSE),
				$query->like('links', $search, FALSE),
				$query->like('link_names', $search, FALSE),
			))
		)->execute();
	}
}
