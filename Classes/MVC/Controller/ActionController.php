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
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
/**
 * Fileman Action Controller.
 *
 * Replacement for the Extbase Action Controller for inheritance by the
 * domain controllers. It unites all fileman-specific code that is
 * to be shared between all domain controllers.
 *
 * @package fileman
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Tx_Fileman_MVC_Controller_ActionController extends Tx_Fileman_MVC_Controller_CsrfProtectController {

	/**
	 * Logged in frontend user
	 *
	 * @var Tx_Fileman_Domain_Model_FrontendUser
	 */
	protected $feUser;

	/**
	 * User service
	 *
	 * @var Tx_Fileman_Service_UserService
	 * @inject
	 */
	protected $userService;

	/**
	 * @var Tx_Fileman_Service_SortRepositoryService
	 */
	protected $sortRepositoryService;

	/**
	 * categoryRepository
	 *
	 * @var Tx_Fileman_Domain_Repository_CategoryRepository
	 */
	protected $categoryRepository;

	/**
	 * Class constructor
	 *
	 * @return void
	 */
	public function __construct() {
		parent::__construct();
		$this->sortRepositoryService = GeneralUtility::makeInstance(ObjectManager::class)->get('Tx_Fileman_Service_SortRepositoryService');
	}
	/**
	 * injectCategoryRepository
	 *
	 * @param Tx_Fileman_Domain_Repository_CategoryRepository $categoryRepository
	 * @return void
	 */
	public function injectCategoryRepository(Tx_Fileman_Domain_Repository_CategoryRepository $categoryRepository) {
		$this->categoryRepository = $categoryRepository;
		$this->sortRepositoryService->registerSortableRepository($categoryRepository, [
			Tx_Fileman_Service_SortRepositoryService::SORT_FIELD_TITLE => 'title'
		]);
	}



	/**
	 * Initializes the controller before invoking an action method.
	 *
	 * @return void
	 */
	protected function initializeAction() {
		parent::initializeAction();
		//get currently logged in user
		$this->feUser = $this->userService->getCurrentUser();
		$this->sortRepositoryService->sortRepositories();
	}

	/**
	 * Adds the needed validators to the Arguments:
	 *
	 * - Validators checking the data type from the @param annotation
	 * - Custom validators specified with validate annotations.
	 * - Model-based validators (validate annotations in the model)
	 * - Custom model validator classes
	 *
	 * This override works around the 6.2-bug where it no longer supports
	 * dontvalidate for the deprecatedPropertyMapper when a matching
	 * Domain Model validator is present.
	 *
	 * @return void
	 */
	protected function initializeActionMethodValidators() {
		if (version_compare(TYPO3_branch, '6.2', '<')) {
			parent::initializeActionMethodValidators();
		} else {
			// @deprecated since Extbase 1.4.0, will be removed two versions after Extbase 6.1

			$parameterValidators = $this->validatorResolver->buildMethodArgumentsValidatorConjunctions(get_class($this), $this->actionMethodName);
			$dontValidateAnnotations = array();

			$methodTagsValues = $this->reflectionService->getMethodTagsValues(get_class($this), $this->actionMethodName);
			if (isset($methodTagsValues['dontvalidate'])) {
				$dontValidateAnnotations = $methodTagsValues['dontvalidate'];
			}

			foreach ($this->arguments as $argument) {
				$validator = $parameterValidators[$argument->getName()];
				if (array_search('$' . $argument->getName(), $dontValidateAnnotations) === FALSE) {
					$baseValidatorConjunction = $this->validatorResolver->getBaseValidatorConjunction($argument->getDataType());
					if ($baseValidatorConjunction !== NULL) {
						$validator->addValidator($baseValidatorConjunction);
					}
					// CHANGE: moved this INSIDE the if, instead of outside
					$argument->setValidator($validator);
				}
			}
		}
	}

	/**
	 * Sort action
	 *
	 * @param string $sorting
	 * @param Tx_Fileman_Domain_Model_Category $category
	 * @dontvalidate $category
	 * @ignorevalidation $category
	 * @return void
	 */
	public function sortAction($sorting, Tx_Fileman_Domain_Model_Category $category = NULL) {
		$this->sortRepositoryService->setSorting($sorting);
		$this->clearCacheOnError();
		$arguments = $category === NULL ? [] : [ 'category' => $category ];
		$this->redirect('list', NULL, NULL, $arguments);
	}
}
?>