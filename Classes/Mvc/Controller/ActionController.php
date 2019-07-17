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
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use TYPO3\CMS\Fluid\View\AbstractTemplateView;
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
class ActionController extends ErrorOnDebugController {

	/**
	 * Logged in frontend user
	 *
	 * @var \TYPO3\CMS\Extbase\Domain\Model\FrontendUser
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
	 * Indicates if user needs to be logged in
	 *
	 * Can be overridden by extending domain controllers
	 *
	 * @var boolean
	 */
	protected $requireLogin = TRUE;

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
	 * @var \Innologi\TYPO3AssetProvider\ProviderServiceInterface
	 * @inject
	 */
	protected $assetProviderService;

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
	 * Disables (or enables) requireLogin by action
	 *
	 * @param array $actions For which to disable requireLogin
	 * @return void
	 */
	protected function disableRequireLogin(array $actions = []) {
		$this->requireLogin = !in_array(substr($this->actionMethodName, 0, -6), $actions);
	}

	/**
	 * Validates a request based on $tokenArgument, through TYPO3's internal
	 * CSRF protection. If invalid, will automatically stop
	 *
	 * @param string $tokenArgument
	 * @return boolean
	 * @throws StopActionException
	 */
	protected function validateRequest($tokenArgument = 'stoken', $id = NULL, $objectType = NULL) {
		/** @var \TYPO3\CMS\Extbase\Mvc\Web\ReferringRequest $referringRequest */
		$referringRequest = $this->request->getReferringRequest();
		if ($referringRequest !== NULL) {
			if ($objectType === NULL) {
				$objectType = strtolower($referringRequest->getControllerName());
			}
			if ($this->request->hasArgument($tokenArgument) &&
				$this->request->hasArgument($objectType) &&
				\TYPO3\CMS\Core\FormProtection\FormProtectionFactory::get()->validateToken(
					$this->request->getArgument($tokenArgument),
					$referringRequest->getControllerName(),
					$referringRequest->getControllerActionName(),
					$id ?? ($this->request->getArgument($objectType)['__identity'] ?? '')
				)
			) {
				return TRUE;
			}
		}

		$this->controllerContext = $this->buildControllerContext();
		$this->addFlashMessage(
			LocalizationUtility::translate('tx_fileman.csrf_invalid', $this->extensionName),
			'',
			FlashMessage::ERROR
		);
		// this will actually end up rebuilding the referringRequest, but who cares, we're in an error state
		$this->errorAction();
		// in case errorAction fails to forward the request
		throw new StopActionException('invalid request', 1503940139);
	}

	/**
	 * Initializes the view before invoking an action method.
	 *
	 * Override this method to solve assign variables common for all actions
	 * or prepare the view in another way before the action is called.
	 *
	 * @param \TYPO3\CMS\Extbase\Mvc\View\ViewInterface $view The view to be initialized
	 *
	 * @return void
	 * @api
	 */
	protected function initializeView(ViewInterface $view) {
		if ($view instanceof AbstractTemplateView && $this->request->getFormat() === 'html') {
			// provide assets as configured per action
			$this->assetProviderService->provideAssets(
			    \strtolower($this->extensionName),
				$this->request->getControllerName(),
				$this->request->getControllerActionName()
			);
		}
	}

	/**
	 * Initializes the controller before invoking an action method.
	 *
	 * @return void
	 */
	protected function initializeAction() {
		parent::initializeAction();
		$errors = [];

		//is user logged in as required?
		$this->feUser = $this->userService->getCurrentUser();
		if ($this->requireLogin && !$this->feUser) {
			$errors[] = LocalizationUtility::translate('tx_fileman.login_error', $this->extensionName);
		}
		//errors!
		if (!empty($errors)) {
			// we'll need it for the FlashMessageQueue
			$this->controllerContext = $this->buildControllerContext();
			foreach ($errors as $flashMessage) {
				$this->addFlashMessage($flashMessage, '', FlashMessage::ERROR);
			}
			$this->redirect('list', 'category');
		}

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