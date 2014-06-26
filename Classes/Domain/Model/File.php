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
 * File Domain Model
 *
 * @package fileman
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Tx_Fileman_Domain_Model_File extends Tx_Extbase_DomainObject_AbstractEntity {
	#@TODO beveiliging van upload-bestanden?
	#@TODO flexform configuratie

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
	 * @transient
	 */
	protected $tmpFile;

	/**
	 * Index of uploaded file
	 *
	 * @var integer
	 * @transient
	 */
	protected $index;

	/**
	 * Displayed name of the file.
	 *
	 * @var string
	 */
	protected $alternateTitle; #@LOW be renamed

	/**
	 * File description
	 *
	 * @var string
	 * @validate Text
	 */
	protected $description;

	/**
	 * Links related to this file (one per row)
	 *
	 * @var string
	 * @validate Tx_Fileman_Domain_Validator_LinksValidator
	 */
	protected $links;

	/**
	 * Array with upload data from the $_FILES array, filled by either rewrittenPropertyManager or fileService
	 *
	 * @var array
	 * @transient
	 */
	protected $uploadData = array();

	/**
	 * Alternative name per link (one per row)
	 *
	 * @var string
	 */
	protected $linkNames; #@LOW currently unused

	/**
	 * Categories related to this file entity
	 *
	 * @var Tx_Extbase_Persistence_ObjectStorage<Tx_Fileman_Domain_Model_Category>
	 * @validate NotEmpty
	 * @lazy
	 */
	protected $category;

	/**
	 * User who created this appointment
	 *
	 * @var Tx_Fileman_Domain_Model_FrontendUser
	 * @lazy
	 */
	protected $feUser;

	/**
	 * Category timestamp
	 *
	 * @var integer
	 */
	protected $tstamp;

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
	 * Returns the filename
	 *
	 * @return string
	 */
	public function getFilename() { #@LOW currently unused
		return basename($this->getFileUri());
	}

	/**
	 * Returns the fileUri
	 *
	 * @return string
	 */
	public function getFileUri() {
		return /*t3lib_div::fixWindowsFilePath(*/$this->fileUri/*)*/; #@TODO have to see if we can get around the TCA requirement
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
	public function getLinksFormatted() { #@LOW work with a transient
		if (isset($this->links[0])) {
			$links = str_replace("\r\n","\n",$this->links);
			$linkArray = t3lib_div::trimExplode("\n", $links,1);
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
	 * Returns the linkNames
	 *
	 * @return string
	 */
	public function getLinkNames() { #@LOW currently unused
		return $this->linkNames;
	}

	/**
	 * Sets the linkNames
	 *
	 * @param string $linkNames
	 * @return void
	 */
	public function setLinkNames($linkNames) { #@LOW currently unused
		$this->linkNames = $linkNames;
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
	 * @return Tx_Extbase_Persistence_ObjectStorage
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
	 * Returns the feUser
	 *
	 * @return Tx_Fileman_Domain_Model_FrontendUser
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

}
?>