<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Frenck Lutke <frenck@innologi.nl>, www.innologi.nl
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
	 */
	protected $userService;

	/**
	 * categoryRepository
	 *
	 * @var Tx_Fileman_Domain_Repository_CategoryRepository
	 */
	protected $categoryRepository;



	/**
	 * Injects the User Service
	 *
	 * @param Tx_Fileman_Service_UserService $userService
	 * @return void
	 */
	public function injectUserService(Tx_Fileman_Service_UserService $userService) {
		$this->userService = $userService;
	}

	/**
	 * injectCategoryRepository
	 *
	 * @param Tx_Fileman_Domain_Repository_CategoryRepository $categoryRepository
	 * @return void
	 */
	public function injectCategoryRepository(Tx_Fileman_Domain_Repository_CategoryRepository $categoryRepository) {
		//default sorting
		$categoryRepository->setDefaultOrderings(array(
				'title' => Tx_Extbase_Persistence_QueryInterface::ORDER_ASCENDING
		));
		$this->categoryRepository = $categoryRepository;
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

}
?>