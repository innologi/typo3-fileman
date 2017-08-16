<?php
namespace Innologi\Fileman\Domain\Repository;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012-2016 Frenck Lutke <typo3@innologi.nl>, www.innologi.nl
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
use TYPO3\CMS\Extbase\Persistence\Repository;
use Innologi\Fileman\Domain\Model\Category;
/**
 * File repository
 *
 * @package fileman
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class FileRepository extends Repository {

	/**
	 * Returns all objects of this repository belonging to the provided category
	 *
	 * @param Category $category The category to show files of
	 * @return array An array of objects, empty if no objects found
	 */
	public function findAllByCategory(Category $category) {
		$query = $this->createQuery();
		$result = $query->matching(
				$query->contains('category', $category)
			)->execute();
		return $result;
	}

	/**
	 * Returns all objects that match all search terms
	 *
	 * @param array $searchTerms
	 * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
	 */
	public function search(array $searchTerms) {
		$query = $this->createQuery();

		$conditions = array();
		foreach ($searchTerms as $searchTerm) {
			$searchTerm = '%' . $searchTerm . '%';
			$conditions[] = $query->logicalOr(array(
				$query->like('file_uri', $searchTerm, FALSE),
				$query->like('alternate_title', $searchTerm, FALSE),
				$query->like('description', $searchTerm, FALSE),
				$query->like('links', $searchTerm, FALSE),
				$query->like('link_names', $searchTerm, FALSE),
			));
		}

		return $query->matching(
			$query->logicalAnd($conditions)
		)->execute();
	}
}
