<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Frenck Lutke <typo3@innologi.nl>, www.innologi.nl
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
 * Link Domain Model
 *
 * @package fileman
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Tx_Fileman_Domain_Model_Link extends Tx_Extbase_DomainObject_AbstractEntity {

	/**
	 * Link URI
	 *
	 * @var string
	 * @validate Tx_Fileman_Domain_Validator_LinkUriValidator
	 */
	protected $linkUri;

	/**
	 * Name of link
	 *
	 * @var string
	 * @validate String
	 */
	protected $linkName;

	/**
	 * Link description
	 *
	 * @var string
	 * @validate Text
	 */
	protected $description;

	/**
	 * User who created this file
	 *
	 * @var Tx_Fileman_Domain_Model_FrontendUser
	 * @lazy
	 */
	protected $feUser; #@TODO some of these things are shared between different models, so might as well extend from an abstract

	/**
	 * Categories related to this link entity
	 *
	 * @var Tx_Extbase_Persistence_ObjectStorage<Tx_Fileman_Domain_Model_Category>
	 * @lazy
	 */
	protected $category;

	/**
	 * Category timestamp
	 *
	 * @var integer
	 */
	protected $tstamp;

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
	 * Initializes all Tx_Extbase_Persistence_ObjectStorage properties.
	 *
	 * @return void
	 */
	protected function initStorageObjects() {
		$this->category = new Tx_Extbase_Persistence_ObjectStorage();
	}

	/**
	 * Returns the linkUri
	 *
	 * @return string $linkUri
	 */
	public function getLinkUri() {
		return $this->linkUri;
	}

	/**
	 * Sets the linkUri
	 *
	 * @param string $linkUri
	 * @return void
	 */
	public function setLinkUri($linkUri) {
		$this->linkUri = $linkUri;
	}

	/**
	 * Returns the linkName
	 *
	 * @return string $linkName
	 */
	public function getLinkName() {
		return $this->linkName;
	}

	/**
	 * Sets the linkName
	 *
	 * @param string $linkName
	 * @return void
	 */
	public function setLinkName($linkName) {
		$this->linkName = trim($linkName);
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
	 * Adds a Category
	 *
	 * @param Tx_Fileman_Domain_Model_Category $category
	 * @return void
	 */
	public function addCategory(Tx_Fileman_Domain_Model_Category $category) {
		$this->category->attach($category);
	}

	/**
	 * Removes a Category
	 *
	 * @param Tx_Fileman_Domain_Model_Category $categoryToRemove The Category to be removed
	 * @return void
	 */
	public function removeCategory(Tx_Fileman_Domain_Model_Category $categoryToRemove) {
		$this->category->detach($categoryToRemove);
	}

	/**
	 * Returns the category
	 *
	 * @return Tx_Extbase_Persistence_ObjectStorage $category
	 */
	public function getCategory() {
		return $this->category;
	}

	/**
	 * Sets the category
	 *
	 * @param Tx_Extbase_Persistence_ObjectStorage $category
	 * @return void
	 */
	public function setCategory(Tx_Extbase_Persistence_ObjectStorage $category) {
		$this->category = $category;
	}

	/**
	 * Returns the timestamp
	 *
	 * @return integer $tstamp
	 */
	public function getTstamp() {
		return $this->tstamp;
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