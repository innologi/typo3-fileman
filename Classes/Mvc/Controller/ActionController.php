<?php
namespace Innologi\Fileman\Mvc\Controller;
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
use Innologi\Fileman\Service\SortRepositoryService;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractCompositeValidator;
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
class ActionController extends CsrfProtectController {

	/**
	 * Logged in frontend user
	 *
	 * @var \Innologi\Fileman\Domain\Model\FrontendUser
	 */
	protected $feUser;

	/**
	 * User service
	 *
	 * @var \Innologi\Fileman\Service\UserService
	 * @inject
	 */
	protected $userService;

	/**
	 * @var \Innologi\Fileman\Service\SortRepositoryService
	 */
	protected $sortRepositoryService;

	/**
	 * categoryRepository
	 *
	 * @var \Innologi\Fileman\Domain\Repository\CategoryRepository
	 */
	protected $categoryRepository;

	/**
	 * Class constructor
	 *
	 * @return void
	 */
	public function __construct() {
		parent::__construct();
		$this->sortRepositoryService = GeneralUtility::makeInstance(ObjectManager::class)->get(SortRepositoryService::class);
	}
	/**
	 * injectCategoryRepository
	 *
	 * @param \Innologi\Fileman\Domain\Repository\CategoryRepository $categoryRepository
	 * @return void
	 */
	public function injectCategoryRepository(\Innologi\Fileman\Domain\Repository\CategoryRepository $categoryRepository) {
		$this->categoryRepository = $categoryRepository;
		$this->sortRepositoryService->registerSortableRepository($categoryRepository, [
			SortRepositoryService::SORT_FIELD_TITLE => 'title'
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
	 * This override allows to truly ignore validation for @ignorevalidation
	 * action method arguments.
	 *
	 * @return void
	 */
	protected function initializeActionMethodValidators() {
		if (version_compare(TYPO3_branch, '8.7', '>')) {
			// @TODO review on TYPO3 v9
			return parent::initializeActionMethodValidators();
		}

		$actionMethodParameters = static::getActionMethodParameters($this->objectManager);
		$methodParameters = $actionMethodParameters[$this->actionMethodName] ?? [];
		$methodTagsValues = $this->reflectionService->getMethodTagsValues(get_class($this), $this->actionMethodName);
		$ignoreArgs = $methodTagsValues['ignorevalidation'] ?? [];
		foreach ($ignoreArgs as $ignore) {
			unset($methodParameters[substr($ignore, 1)]);
		}

		$parameterValidators = $this->validatorResolver->buildMethodArgumentsValidatorConjunctions(get_class($this), $this->actionMethodName, $methodParameters);
		/** @var \TYPO3\CMS\Extbase\Mvc\Controller\Argument $argument */
		foreach ($this->arguments as $argument) {
			if (!isset($parameterValidators[$argument->getName()])) {
				continue;
			}
			$validator = $parameterValidators[$argument->getName()];

			$baseValidatorConjunction = $this->validatorResolver->getBaseValidatorConjunction($argument->getDataType());
			if (!empty($baseValidatorConjunction) && $validator instanceof AbstractCompositeValidator) {
				$validator->addValidator($baseValidatorConjunction);
			}
			$argument->setValidator($validator);
		}
	}

	/**
	 * Sort action
	 *
	 * @param string $sorting
	 * @param \Innologi\Fileman\Domain\Model\Category $category
	 * @ignorevalidation $category
	 * @return void
	 */
	public function sortAction($sorting, \Innologi\Fileman\Domain\Model\Category $category = NULL) {
		$this->sortRepositoryService->setSorting($sorting);
		$this->clearCacheOnError();
		$arguments = $category === NULL ? [] : [ 'category' => $category ];
		$this->redirect('list', NULL, NULL, $arguments);
	}
}