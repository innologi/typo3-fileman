<?php
namespace Innologi\Fileman\Domain\Model;
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
use TYPO3\CMS\Extbase\DomainObject\AbstractValueObject;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
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
class FileStorage extends AbstractValueObject {
	// @LOW can we get rid of this class at some point?
	/**
	 * Contains files
	 *
	 * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Innologi\Fileman\Domain\Model\File>
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
	 * Initializes all \TYPO3\CMS\Extbase\Persistence\ObjectStorage properties.
	 *
	 * @return void
	 */
	protected function initStorageObjects() {
		$this->file = new ObjectStorage();
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
	 * Returns file
	 *
	 * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage
	 */
	public function getFile() {
		return $this->file;
	}

	/**
	 * Sets file
	 *
	 * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $file
	 * @return void
	 */
	public function setFile(ObjectStorage $file) {
		$this->file = $file;
	}

}