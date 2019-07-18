<?php
namespace Innologi\Fileman\Domain\Model;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012-2019 Frenck Lutke <typo3@innologi.nl>, www.innologi.nl
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
use TYPO3\CMS\Extbase\Domain\Model\FrontendUser;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
/**
 * Link Domain Model
 *
 * @package fileman
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Link extends AbstractEntity {

	/**
	 * Link URI
	 *
	 * @var string
	 * @extensionScannerIgnoreLine
	 * @validate NotEmpty,\Innologi\Fileman\Domain\Validator\LinkUriValidator
	 * @TYPO3\CMS\Extbase\Annotation\Validate("NotEmpty")
	 * @TYPO3\CMS\Extbase\Annotation\Validate("\Innologi\Fileman\Domain\Validator\LinkUriValidator")
	 */
	protected $linkUri;

	/**
	 * Name of link
	 *
	 * @var string
	 * @extensionScannerIgnoreLine
	 * @validate Text
	 * @TYPO3\CMS\Extbase\Annotation\Validate("Text")
	 */
	protected $linkName;

	/**
	 * Link description
	 *
	 * @var string
	 * @extensionScannerIgnoreLine
	 * @validate Text
	 * @TYPO3\CMS\Extbase\Annotation\Validate("Text")
	 */
	protected $description = '';

	/**
	 * User who created this file
	 *
	 * @var \TYPO3\CMS\Extbase\Domain\Model\FrontendUser
	 * @extensionScannerIgnoreLine
	 * @lazy
	 * @TYPO3\CMS\Extbase\Annotation\ORM\Lazy
	 */
	protected $feUser; #@TODO some of these things are shared between different models, so might as well extend from an abstract

	/**
	 * Categories related to this link entity
	 *
	 * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Innologi\Fileman\Domain\Model\Category>
	 * @extensionScannerIgnoreLine
	 * @lazy
	 * @TYPO3\CMS\Extbase\Annotation\ORM\Lazy
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
	 * Initializes all \TYPO3\CMS\Extbase\Persistence\ObjectStorage properties.
	 *
	 * @return void
	 */
	protected function initStorageObjects() {
		$this->category = new ObjectStorage();
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
	 * @return \TYPO3\CMS\Extbase\Domain\Model\FrontendUser feUser
	 */
	public function getFeUser() {
		return $this->feUser;
	}

	/**
	 * Sets the feUser
	 *
	 * @param \TYPO3\CMS\Extbase\Domain\Model\FrontendUser $feUser
	 * @return void
	 */
	public function setFeUser(FrontendUser $feUser) {
		$this->feUser = $feUser;
	}

	/**
	 * Adds a Category
	 *
	 * @param \Innologi\Fileman\Domain\Model\Category $category
	 * @return void
	 */
	public function addCategory(Category $category) {
		$this->category->attach($category);
	}

	/**
	 * Removes a Category
	 *
	 * @param \Innologi\Fileman\Domain\Model\Category $categoryToRemove The Category to be removed
	 * @return void
	 */
	public function removeCategory(Category $categoryToRemove) {
		$this->category->detach($categoryToRemove);
	}

	/**
	 * Returns the category
	 *
	 * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage $category
	 */
	public function getCategory() {
		return $this->category;
	}

	/**
	 * Sets the category
	 *
	 * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $category
	 * @return void
	 */
	public function setCategory(ObjectStorage $category) {
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