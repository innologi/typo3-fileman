<?php
namespace Innologi\Fileman\Domain\Model;
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
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
/**
 * Category Domain Model
 *
 * @package fileman
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Category extends AbstractEntity {

	/**
	 * Category title
	 *
	 * @var string
	 * @validate NotEmpty,String
	 */
	protected $title;

	/**
	 * Category description
	 *
	 * @var string
	 * @validate Text
	 */
	protected $description;

	/**
	 * Subcategories
	 *
	 * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Innologi\Fileman\Domain\Model\Category>
	 * @lazy
	 */
	protected $subCategory;

	/**
	 * Files within this category
	 *
	 * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Innologi\Fileman\Domain\Model\File>
	 * @lazy
	 */
	protected $file;

	/**
	 * Links within this category
	 *
	 * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Innologi\Fileman\Domain\Model\Link>
	 * @lazy
	 */
	protected $link;

	/**
	 * Parent-categories
	 *
	 * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Innologi\Fileman\Domain\Model\Category>
	 * @lazy
	 */
	protected $parentCategory;

	/**
	 * Sum of $link and $file counts
	 *
	 * @var Integer
	 * @transient
	 */
	protected $count;

	/**
	 * Owner of category
	 *
	 * @var \Innologi\Fileman\Domain\Model\FrontendUser
	 */
	protected $feUser;

	/**
	 * Category fe_group
	 *
	 * @var string
	 */
	protected $feGroup;

	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct() {
		$this->initStorageObjects();
	}

	/**
	 * Initializes all \TYPO3\CMS\Extbase\Persistence\ObjectStorage properties.
	 *
	 * @return void
	 */
	protected function initStorageObjects() {
		$this->file = new ObjectStorage();
		$this->link = new ObjectStorage();
		$this->subCategory = new ObjectStorage();
		$this->parentCategory = new ObjectStorage();
	}

	/**
	 * Returns the title
	 *
	 * @return string $title
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * Sets the title
	 *
	 * @param string $title
	 * @return void
	 */
	public function setTitle($title) {
		$this->title = trim($title);
	}

	/**
	 * Returns the description
	 *
	 * @return string $description
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * Sets the description
	 *
	 * @param string $description
	 * @return void
	 */
	public function setDescription($description) {
		$this->description = trim($description);
	}

	/**
	 * Adds a SubCategory
	 *
	 * @param \Innologi\Fileman\Domain\Model\Category $subCategory
	 * @return void
	 */
	public function addSubCategory(Category $subCategory) {
		$this->subCategory->attach($subCategory);
	}

	/**
	 * Removes a SubCategory
	 *
	 * @param \Innologi\Fileman\Domain\Model\Category $subCategoryToRemove The SubCategory to be removed
	 * @return void
	 */
	public function removeSubCategory(Category $subCategoryToRemove) {
		$this->subCategory->detach($subCategoryToRemove);
	}

	/**
	 * Returns the subCategory
	 *
	 * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage $subCategory
	 */
	public function getSubCategory() {
		return $this->subCategory;
	}

	/**
	 * Sets the subCategory
	 *
	 * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $subCategory
	 * @return void
	 */
	public function setSubCategory(ObjectStorage $subCategory) {
		$this->subCategory = $subCategory;
	}

	/**
	 * Adds a File
	 *
	 * @param \Innologi\Fileman\Domain\Model\File $file
	 * @return void
	 */
	public function addFile(File $file) {
		$this->file->attach($file);
	}

	/**
	 * Removes a File
	 *
	 * @param \Innologi\Fileman\Domain\Model\File $fileToRemove The File to be removed
	 * @return void
	 */
	public function removeFile(File $fileToRemove) {
		$this->file->detach($fileToRemove);
	}

	/**
	 * Returns the file
	 *
	 * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage $file
	 */
	public function getFile() {
		return $this->file;
	}

	/**
	 * Sets the file
	 *
	 * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $file
	 * @return void
	 */
	public function setFile(ObjectStorage $file) {
		$this->file = $file;
	}

	/**
	 * Adds a Link
	 *
	 * @param \Innologi\Fileman\Domain\Model\Link $link
	 * @return void
	 */
	public function addLink(Link $link) {
		$this->link->attach($link);
	}

	/**
	 * Removes a Link
	 *
	 * @param \Innologi\Fileman\Domain\Model\Link $linkToRemove The Link to be removed
	 * @return void
	 */
	public function removeLink(Link $linkToRemove) {
		$this->link->detach($linkToRemove);
	}

	/**
	 * Returns the link
	 *
	 * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage $link
	 */
	public function getLink() {
		return $this->link;
	}

	/**
	 * Sets the link
	 *
	 * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $link
	 * @return void
	 */
	public function setLink(ObjectStorage $link) {
		$this->link = $link;
	}

	/**
	 * Adds a ParentCategory
	 *
	 * @param \Innologi\Fileman\Domain\Model\Category $parentCategory
	 * @return void
	 */
	public function addParentCategory(Category $parentCategory) {
		$this->parentCategory->attach($parentCategory);
	}

	/**
	 * Removes a ParentCategory
	 *
	 * @param \Innologi\Fileman\Domain\Model\Category $parentCategoryToRemove The ParentCategory to be removed
	 * @return void
	 */
	public function removeParentCategory(Category $parentCategoryToRemove) {
		$this->parentCategory->detach($parentCategoryToRemove);
	}

	/**
	 * Returns the parentCategory
	 *
	 * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage $parentCategory
	 */
	public function getParentCategory() {
		return $this->parentCategory;
	}

	/**
	 * Sets the parentCategory
	 *
	 * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $parentCategory
	 * @return void
	 */
	public function setParentCategory(ObjectStorage $parentCategory) {
		$this->parentCategory = $parentCategory;
	}

	/**
	 * Returns the feUser
	 *
	 * @return \Innologi\Fileman\Domain\Model\FrontendUser feUser
	 */
	public function getFeUser() {
		return $this->feUser;
	}

	/**
	 * Sets the feUser
	 *
	 * @param \Innologi\Fileman\Domain\Model\FrontendUser $feUser
	 * @return void
	 */
	public function setFeUser(FrontendUser $feUser) {
		$this->feUser = $feUser;
	}

	/**
	 * Returns count
	 *
	 * @return integer
	 */
	public function getCount() {
		$this->count = $this->file->count() + $this->link->count() + $this->subCategory->count();
		return $this->count;
	}

	/**
	 * Return fe_group
	 *
	 * @return string
	 */
	public function getFeGroup() {
		return $this->feGroup;
	}

	/**
	 * Sets fe_group
	 *
	 * @param string $feGroup
	 * @return $this
	 */
	public function setFeGroup($feGroup) {
		$this->feGroup = $feGroup;
		return $this;
	}

}