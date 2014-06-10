<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Frenck Lutke <frenck@innologi.nl>, www.innologi.nl
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
 * Category Domain Model
 *
 * @package fileman
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Tx_Fileman_Domain_Model_Category extends Tx_Extbase_DomainObject_AbstractEntity {

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
	 * @var Tx_Extbase_Persistence_ObjectStorage<Tx_Fileman_Domain_Model_Category>
	 * @lazy
	 */
	protected $subCategory;

	/**
	 * Files within this category
	 *
	 * @var Tx_Extbase_Persistence_ObjectStorage<Tx_Fileman_Domain_Model_File>
	 * @lazy
	 */
	protected $file;

	/**
	 * Links within this category
	 *
	 * @var Tx_Extbase_Persistence_ObjectStorage<Tx_Fileman_Domain_Model_Link>
	 * @lazy
	 */
	protected $link;

	/**
	 * Parent-categories
	 *
	 * @var Tx_Extbase_Persistence_ObjectStorage<Tx_Fileman_Domain_Model_Category>
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
	 * User who created this appointment
	 *
	 * @var Tx_Fileman_Domain_Model_FrontendUser
	 * @lazy
	 */
	protected $feUser;

	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct() {
		$this->initStorageObjects();
	}

	/**
	 * Initializes all Tx_Extbase_Persistence_ObjectStorage properties.
	 *
	 * @return void
	 */
	protected function initStorageObjects() {
		$this->file = new Tx_Extbase_Persistence_ObjectStorage();
		$this->link = new Tx_Extbase_Persistence_ObjectStorage();
		$this->subCategory = new Tx_Extbase_Persistence_ObjectStorage();
		$this->parentCategory = new Tx_Extbase_Persistence_ObjectStorage();
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
	 * @param Tx_Fileman_Domain_Model_Category $subCategory
	 * @return void
	 */
	public function addSubCategory(Tx_Fileman_Domain_Model_Category $subCategory) {
		$this->subCategory->attach($subCategory);
	}

	/**
	 * Removes a SubCategory
	 *
	 * @param Tx_Fileman_Domain_Model_Category $subCategoryToRemove The SubCategory to be removed
	 * @return void
	 */
	public function removeSubCategory(Tx_Fileman_Domain_Model_Category $subCategoryToRemove) {
		$this->subCategory->detach($subCategoryToRemove);
	}

	/**
	 * Returns the subCategory
	 *
	 * @return Tx_Extbase_Persistence_ObjectStorage $subCategory
	 */
	public function getSubCategory() {
		return $this->subCategory;
	}

	/**
	 * Sets the subCategory
	 *
	 * @param Tx_Extbase_Persistence_ObjectStorage $subCategory
	 * @return void
	 */
	public function setSubCategory(Tx_Extbase_Persistence_ObjectStorage $subCategory) {
		$this->subCategory = $subCategory;
	}

	/**
	 * Adds a File
	 *
	 * @param Tx_Fileman_Domain_Model_File $file
	 * @return void
	 */
	public function addFile(Tx_Fileman_Domain_Model_File $file) {
		$this->file->attach($file);
	}

	/**
	 * Removes a File
	 *
	 * @param Tx_Fileman_Domain_Model_File $fileToRemove The File to be removed
	 * @return void
	 */
	public function removeFile(Tx_Fileman_Domain_Model_File $fileToRemove) {
		$this->file->detach($fileToRemove);
	}

	/**
	 * Returns the file
	 *
	 * @return Tx_Extbase_Persistence_ObjectStorage $file
	 */
	public function getFile() {
		return $this->file;
	}

	/**
	 * Sets the file
	 *
	 * @param Tx_Extbase_Persistence_ObjectStorage $file
	 * @return void
	 */
	public function setFile(Tx_Extbase_Persistence_ObjectStorage $file) {
		$this->file = $file;
	}

	/**
	 * Adds a Link
	 *
	 * @param Tx_Fileman_Domain_Model_Link $link
	 * @return void
	 */
	public function addLink(Tx_Fileman_Domain_Model_Link $link) {
		$this->link->attach($link);
	}

	/**
	 * Removes a Link
	 *
	 * @param Tx_Fileman_Domain_Model_Link $linkToRemove The Link to be removed
	 * @return void
	 */
	public function removeLink(Tx_Fileman_Domain_Model_Link $linkToRemove) {
		$this->link->detach($linkToRemove);
	}

	/**
	 * Returns the link
	 *
	 * @return Tx_Extbase_Persistence_ObjectStorage $link
	 */
	public function getLink() {
		return $this->link;
	}

	/**
	 * Sets the link
	 *
	 * @param Tx_Extbase_Persistence_ObjectStorage $link
	 * @return void
	 */
	public function setLink(Tx_Extbase_Persistence_ObjectStorage $link) {
		$this->link = $link;
	}

	/**
	 * Adds a ParentCategory
	 *
	 * @param Tx_Fileman_Domain_Model_Category $parentCategory
	 * @return void
	 */
	public function addParentCategory(Tx_Fileman_Domain_Model_Category $parentCategory) {
		$this->parentCategory->attach($parentCategory);
	}

	/**
	 * Removes a ParentCategory
	 *
	 * @param Tx_Fileman_Domain_Model_Category $parentCategoryToRemove The ParentCategory to be removed
	 * @return void
	 */
	public function removeParentCategory(Tx_Fileman_Domain_Model_Category $parentCategoryToRemove) {
		$this->parentCategory->detach($parentCategoryToRemove);
	}

	/**
	 * Returns the parentCategory
	 *
	 * @return Tx_Extbase_Persistence_ObjectStorage $parentCategory
	 */
	public function getParentCategory() {
		return $this->parentCategory;
	}

	/**
	 * Sets the parentCategory
	 *
	 * @param Tx_Extbase_Persistence_ObjectStorage $parentCategory
	 * @return void
	 */
	public function setParentCategory(Tx_Extbase_Persistence_ObjectStorage $parentCategory) {
		$this->parentCategory = $parentCategory;
	}

	/**
	 * Returns the feUser
	 *
	 * @return Tx_Fileman_Domain_Model_FrontendUser feUser
	 */
	public function getFeUser() {
		return $this->feUser;
	}

	/**
	 * Sets the feUser
	 *
	 * @param Tx_Fileman_Domain_Model_FrontendUser $feUser
	 * @return void
	 */
	public function setFeUser(Tx_Fileman_Domain_Model_FrontendUser $feUser) {
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

}
?>