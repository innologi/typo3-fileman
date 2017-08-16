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
 *  the Free Software Foundation; either version 2 of the License, or
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
 * Test case for class Tx_Fileman_Domain_Model_File.
 *
 * @version $Id$
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 * @package TYPO3
 * @subpackage File Manager
 *
 * @author Frenck Lutke <typo3@innologi.nl>
 */
class Tx_Fileman_Domain_Model_FileTest extends Tx_Extbase_Tests_Unit_BaseTestCase {
	/**
	 * @var Tx_Fileman_Domain_Model_File
	 */
	protected $fixture;

	public function setUp() {
		$this->fixture = new Tx_Fileman_Domain_Model_File();
	}

	public function tearDown() {
		unset($this->fixture);
	}

	/**
	 * @test
	 */
	public function getFilenameReturnsInitialValueForString() { }

	/**
	 * @test
	 */
	public function setFilenameForStringSetsFilename() { 
		$this->fixture->setFilename('Conceived at T3CON10');

		$this->assertSame(
			'Conceived at T3CON10',
			$this->fixture->getFilename()
		);
	}
	
	/**
	 * @test
	 */
	public function getFileUriReturnsInitialValueForString() { }

	/**
	 * @test
	 */
	public function setFileUriForStringSetsFileUri() { 
		$this->fixture->setFileUri('Conceived at T3CON10');

		$this->assertSame(
			'Conceived at T3CON10',
			$this->fixture->getFileUri()
		);
	}
	
	/**
	 * @test
	 */
	public function getAlternateTitleReturnsInitialValueForString() { }

	/**
	 * @test
	 */
	public function setAlternateTitleForStringSetsAlternateTitle() { 
		$this->fixture->setAlternateTitle('Conceived at T3CON10');

		$this->assertSame(
			'Conceived at T3CON10',
			$this->fixture->getAlternateTitle()
		);
	}
	
	/**
	 * @test
	 */
	public function getDescriptionReturnsInitialValueForString() { }

	/**
	 * @test
	 */
	public function setDescriptionForStringSetsDescription() { 
		$this->fixture->setDescription('Conceived at T3CON10');

		$this->assertSame(
			'Conceived at T3CON10',
			$this->fixture->getDescription()
		);
	}
	
	/**
	 * @test
	 */
	public function getLinksReturnsInitialValueForString() { }

	/**
	 * @test
	 */
	public function setLinksForStringSetsLinks() { 
		$this->fixture->setLinks('Conceived at T3CON10');

		$this->assertSame(
			'Conceived at T3CON10',
			$this->fixture->getLinks()
		);
	}
	
	/**
	 * @test
	 */
	public function getLinkNamesReturnsInitialValueForString() { }

	/**
	 * @test
	 */
	public function setLinkNamesForStringSetsLinkNames() { 
		$this->fixture->setLinkNames('Conceived at T3CON10');

		$this->assertSame(
			'Conceived at T3CON10',
			$this->fixture->getLinkNames()
		);
	}
	
	/**
	 * @test
	 */
	public function getCategoryReturnsInitialValueForObjectStorageContainingTx_Fileman_Domain_Model_Category() { 
		$newObjectStorage = new Tx_Extbase_Persistence_ObjectStorage();
		$this->assertEquals(
			$newObjectStorage,
			$this->fixture->getCategory()
		);
	}

	/**
	 * @test
	 */
	public function setCategoryForObjectStorageContainingTx_Fileman_Domain_Model_CategorySetsCategory() { 
		$category = new Tx_Fileman_Domain_Model_Category();
		$objectStorageHoldingExactlyOneCategory = new Tx_Extbase_Persistence_ObjectStorage();
		$objectStorageHoldingExactlyOneCategory->attach($category);
		$this->fixture->setCategory($objectStorageHoldingExactlyOneCategory);

		$this->assertSame(
			$objectStorageHoldingExactlyOneCategory,
			$this->fixture->getCategory()
		);
	}
	
	/**
	 * @test
	 */
	public function addCategoryToObjectStorageHoldingCategory() {
		$category = new Tx_Fileman_Domain_Model_Category();
		$objectStorageHoldingExactlyOneCategory = new Tx_Extbase_Persistence_ObjectStorage();
		$objectStorageHoldingExactlyOneCategory->attach($category);
		$this->fixture->addCategory($category);

		$this->assertEquals(
			$objectStorageHoldingExactlyOneCategory,
			$this->fixture->getCategory()
		);
	}

	/**
	 * @test
	 */
	public function removeCategoryFromObjectStorageHoldingCategory() {
		$category = new Tx_Fileman_Domain_Model_Category();
		$localObjectStorage = new Tx_Extbase_Persistence_ObjectStorage();
		$localObjectStorage->attach($category);
		$localObjectStorage->detach($category);
		$this->fixture->addCategory($category);
		$this->fixture->removeCategory($category);

		$this->assertEquals(
			$localObjectStorage,
			$this->fixture->getCategory()
		);
	}
	
}
?>