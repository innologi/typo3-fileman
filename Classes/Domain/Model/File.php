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
use TYPO3\CMS\Extbase\Domain\Model\FrontendUser;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
/**
 * File Domain Model
 *
 * Validation is defined from within the OptionFileValidator class,
 * so that we can exert control over how validation is handled without
 * overruling Core classes again.
 *
 * Objects/ObjectStorages being forced to validate is no problem as our
 * use-case for exerting control does not concern those properties.
 *
 * @package fileman
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class File extends AbstractEntity {

	/**
	 * Filepath
	 *
	 * @var string
	 */
	protected $fileUri;

	/**
	 * Temporary upload file path
	 *
	 * @var string
	 * @extensionScannerIgnoreLine
	 * @transient
	 * @TYPO3\CMS\Extbase\Annotation\ORM\Transient
	 */
	protected $tmpFile;

	/**
	 * Index of uploaded file
	 *
	 * @var integer
	 * @extensionScannerIgnoreLine
	 * @transient
	 * @TYPO3\CMS\Extbase\Annotation\ORM\Transient
	 */
	protected $index;

	// @LOW rename
	/**
	 * Displayed name of the file.
	 *
	 * @var string
	 */
	protected $alternateTitle;

	/**
	 * File description
	 *
	 * @var string
	 */
	protected $description = '';

	/**
	 * Links related to this file (one per row)
	 *
	 * @var string
	 */
	protected $links = '';

	/**
	 * Array with upload data from the $_FILES array, filled by either rewrittenPropertyManager or fileService
	 *
	 * @var array
	 * @extensionScannerIgnoreLine
	 * @transient
	 * @TYPO3\CMS\Extbase\Annotation\ORM\Transient
	 */
	protected $uploadData = [];

	/**
	 * Categories related to this file entity
	 *
	 * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Innologi\Fileman\Domain\Model\Category>
	 * @extensionScannerIgnoreLine
	 * @lazy
	 * @TYPO3\CMS\Extbase\Annotation\ORM\Lazy
	 */
	protected $category;

	/**
	 * User who created this file
	 *
	 * @var \TYPO3\CMS\Extbase\Domain\Model\FrontendUser
	 * @extensionScannerIgnoreLine
	 * @lazy
	 * @TYPO3\CMS\Extbase\Annotation\ORM\Lazy
	 */
	protected $feUser;

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
	 * Returns the filename
	 *
	 * @return string
	 */
	public function getFilename() { // @LOW currently unused
		return basename($this->getFileUri());
	}

	/**
	 * Returns the fileUri
	 *
	 * @return string
	 */
	public function getFileUri() {
		return $this->fileUri; // @TODO replace with FAL?
	}

	/**
	 * Sets the fileUri
	 *
	 * @param string $fileUri
	 * @return void
	 */
	public function setFileUri($fileUri) {
		$this->fileUri = $fileUri;
	}

	/**
	 * Returns the tmpFile
	 *
	 * @return string
	 */
	public function getTmpFile() {
		return $this->tmpFile;
	}

	/**
	 * Sets the tmpFile
	 *
	 * @param string $tmpFile
	 * @return void
	 */
	public function setTmpFile($tmpFile) {
		$this->tmpFile = $tmpFile;
	}

	/**
	 * Returns the index
	 *
	 * @return string
	 */
	public function getIndex() {
		return $this->index;
	}

	/**
	 * Sets the index
	 *
	 * @param string $index
	 * @return void
	 */
	public function setIndex($index) {
		$this->index = $index;
	}

	/**
	 * Returns the alternateTitle
	 *
	 * @return string
	 */
	public function getAlternateTitle() {
		return $this->alternateTitle;
	}

	/**
	 * Sets the alternateTitle
	 *
	 * @param string $alternateTitle
	 * @return void
	 */
	public function setAlternateTitle($alternateTitle) {
		$this->alternateTitle = trim($alternateTitle);
	}

	/**
	 * Returns the description
	 *
	 * @return string
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
	 * Returns the links as array
	 *
	 * @return string
	 */
	public function getLinks() {
		return $this->links;
	}

	/**
	 * Returns the links as array
	 *
	 * @return array
	 */
	public function getLinksFormatted() { // @LOW work with a transient
		if (isset($this->links[0])) {
			$links = str_replace("\r\n","\n",$this->links);
			$linkArray = GeneralUtility::trimExplode("\n", $links,1);
			return $linkArray;
		}
		return array();
	}

	/**
	 * Sets the links
	 *
	 * @param string $links
	 * @return void
	 */
	public function setLinks($links) {
		$this->links = trim($links);
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
	 * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage
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
	 * Returns the feUser
	 *
	 * @return \TYPO3\CMS\Extbase\Domain\Model\FrontendUser
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
	 * Returns uploadData
	 *
	 * @return array
	 */
	public function getUploadData() {
		return $this->uploadData;
	}

	/**
	 * Set uploadData, should correspond to properties from $_FILES
	 *
	 * @param array $uploadData
	 * @return void
	 */
	public function setUploadData(array $uploadData) {
		$this->uploadData = $uploadData;
	}

	/**
	 * Returns the timestamp
	 *
	 * @return integer
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