<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Frenck Lutke <typo3@innologi.nl>, www.innologi.nl
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
 * FileStorage Domain Model
 *
 * Its only purpose is to make extbase play nice with the objectStorage property, as it HAS to
 * be contained in a domain model, sadly. This model is not to be persisted.
 *
 * @package fileman
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Tx_Fileman_Domain_Model_FileStorage extends Tx_Extbase_DomainObject_AbstractValueObject {

	/**
	 * Contains files
	 *
	 * @var Tx_Extbase_Persistence_ObjectStorage<Tx_Fileman_Domain_Model_File>
	 * @validate Tx_Fileman_Domain_Validator_ObjectStorageValidator
	 */
	protected $file;

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
	 * Returns file
	 *
	 * @return Tx_Extbase_Persistence_ObjectStorage
	 */
	public function getFile() {
		return $this->file;
	}

	/**
	 * Sets file
	 *
	 * @param Tx_Extbase_Persistence_ObjectStorage $file
	 * @return void
	 */
	public function setFile(Tx_Extbase_Persistence_ObjectStorage $file) {
		$this->file = $file;
	}

}
?>