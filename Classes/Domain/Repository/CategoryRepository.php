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
use Innologi\Fileman\Domain\Model\FrontendUser;
/**
 * Category repository
 *
 * @package fileman
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class CategoryRepository extends Repository {

	/**
	 * Returns all objects of this repository that are in the root (no parents)
	 *
	 * @param Category $excludeCategory
	 * @return array An array of objects, empty if no objects found
	 */
	public function findInRoot(Category $excludeCategory = NULL) {
		$query = $this->createQuery();

		$conditions = array(
			$query->equals('parentCategory', 0)
		);
		if ($excludeCategory !== NULL) {
			$conditions[] = $query->logicalNot(
				$query->equals('uid', $excludeCategory->getUid())
			);
		}

		return $query->matching(
			$query->logicalAnd($conditions)
		)->execute();
	}

	/**
	 * Returns all objects of this repository belonging to the provided category
	 *
	 * @param Category $category The category to show subcategories of
	 * @return array An array of objects, empty if no objects found
	 */
	public function findAllByParentCategory(Category $category) {
		$query = $this->createQuery();
		$result = $query->matching(
			$query->contains('parentCategory', $category)
		)->execute();
		return $result;
	}

	/**
	 * Returns all objects with feUser set
	 *
	 * @param FrontendUser $feUser
	 * @param Category $excludeCategory
	 * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
	 */
	public function findByFeUser(FrontendUser $feUser, Category $excludeCategory = NULL) {
		$query = $this->createQuery();

		$conditions = array(
			$query->equals('feUser', $feUser)
		);
		if ($excludeCategory !== NULL) {
			$conditions[] = $query->logicalNot(
				$query->equals('uid', $excludeCategory->getUid())
			);
		}

		return $query->matching(
			$query->logicalAnd($conditions)
		)->execute();
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
				$query->like('title', $searchTerm, FALSE),
				$query->like('description', $searchTerm, FALSE),
			));
		}

		return $query->matching(
			$query->logicalAnd($conditions)
		)->execute();
	}

}