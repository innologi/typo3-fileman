<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2016 Frenck Lutke <typo3@innologi.nl>, www.innologi.nl
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
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
/**
 * Service to ease the automatic sorting configuration of repositories
 *
 * @package fileman
 * @author Frenck Lutke <typo3@innologi.nl>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Tx_Fileman_Service_SortRepositoryService implements SingletonInterface{

	const SORT_FIELD_TITLE = 1;
	const SORT_FIELD_CREATION_TIME = 2;
	const SORT_FIELD_UPDATE_TIME = 3;

	const SORT_ORDER_ASC = 1;
	const SORT_ORDER_DESC = 2;

	/**
	 * @var array
	 */
	protected $querySortOrder = [
		self::SORT_ORDER_ASC => QueryInterface::ORDER_ASCENDING,
		self::SORT_ORDER_DESC => QueryInterface::ORDER_DESCENDING
	];

	/**
	 * User service
	 *
	 * @var Tx_Fileman_Service_UserService
	 * @inject
	 */
	protected $userService;

	/**
	 * Sortable repositories
	 *
	 * @var array
	 */
	protected $sortableRepositories = [];

	/**
	 * Session key
	 *
	 * @var string
	 */
	protected $sessionKey = 'tx_fileman_sorting';

	/**
	 * @var array
	 */
	protected $sortingChoices;

	/**
	 * @var string
	 */
	protected $currentValue;

	/**
	 * Set repository sorting
	 *
	 * @param string $sorting
	 * @return void
	 */
	public function setSorting($sorting) {
		$sortData = join('::', GeneralUtility::intExplode('::', $sorting, true));
		$this->userService->putSessionData($this->sessionKey, $sortData);
		$this->currentValue = $sortData;
	}

	/**
	 * Register repository
	 *
	 * @param Repository $repository
	 * @param array $mappings
	 */
	public function registerSortableRepository(Repository $repository, array $mappings) {
		$this->sortableRepositories[] = [
			'repository' => $repository,
			'mappings' => $mappings + [
				self::SORT_FIELD_CREATION_TIME => 'crdate',
				self::SORT_FIELD_UPDATE_TIME => 'tstamp'
			]
		];
	}

	/**
	 * Sorts all repositories that were registered
	 *
	 * @return void
	 * @see $this->registerSortableRepository()
	 */
	public function sortRepositories() {
		if (!empty($this->sortableRepositories)) {
			$sortData = $this->getCurrentValue();
			if (isset($sortData[0])) {
				list($sortField, $sortOrder) = explode('::', $sortData);
			} else {
				$sortField = self::SORT_FIELD_TITLE;
				$sortOrder = self::SORT_ORDER_ASC;
			}

			foreach ($this->sortableRepositories as $repositoryData) {
				// @TODO improve validation of values
				if (isset($repositoryData['mappings'][$sortField]) && isset($this->querySortOrder[$sortOrder])) {
					$repositoryData['repository']->setDefaultOrderings([
						$repositoryData['mappings'][$sortField] => $this->querySortOrder[$sortOrder]
					]);
				}
			}
		}
	}

	/**
	 * Returns all possible sorting choices
	 *
	 * @return array
	 */
	public function getSortingChoices() {
		if ($this->sortingChoices === NULL) {
			$this->sortingChoices = [
				// @TODO make this dynamic / configurable
				// @TODO llang
				self::SORT_FIELD_TITLE . '::' . self::SORT_ORDER_ASC => 'Titel: Oplopend',
				self::SORT_FIELD_TITLE . '::' . self::SORT_ORDER_DESC => 'Titel: Aflopend',
				self::SORT_FIELD_CREATION_TIME . '::' . self::SORT_ORDER_ASC => 'Vroegst Aangemaakt',
				self::SORT_FIELD_CREATION_TIME . '::' . self::SORT_ORDER_DESC => 'Laatst Aangemaakt',
				self::SORT_FIELD_UPDATE_TIME . '::' . self::SORT_ORDER_DESC => 'Laatst Gewijzigd',
			];
		}
		return $this->sortingChoices;
	}

	/**
	 * Returns current sorting value
	 *
	 * @return string
	 */
	public function getCurrentValue() {
		if ($this->currentValue === NULL) {
			$this->currentValue = $this->userService->getSessionData($this->sessionKey);
			if ($this->currentValue === NULL) {
				$this->currentValue = key($this->getSortingChoices());
			}
		}
		return $this->currentValue;
	}

}