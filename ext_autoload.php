<?php
// DO NOT CHANGE THIS FILE! It is automatically generated by extdeveval::buildAutoloadRegistry.
// This file was generated on 2013-08-06 17:55


$extensionPath = t3lib_extMgm::extPath('fileman');
$extensionClassesPath = $extensionPath . 'Classes/';
return array(
	'tx_fileman_controller_categorycontroller' => $extensionClassesPath . 'Controller/CategoryController.php',
	'tx_fileman_controller_categorycontrollertest' => $extensionPath . 'Tests/Unit/Controller/CategoryControllerTest.php',
	'tx_fileman_controller_filecontroller' => $extensionClassesPath . 'Controller/FileController.php',
	'tx_fileman_controller_filecontrollertest' => $extensionPath . 'Tests/Unit/Controller/FileControllerTest.php',
	'tx_fileman_controller_linkcontroller' => $extensionClassesPath . 'Controller/LinkController.php',
	'tx_fileman_domain_model_category' => $extensionClassesPath . 'Domain/Model/Category.php',
	'tx_fileman_domain_model_categorytest' => $extensionPath . 'Tests/Unit/Domain/Model/CategoryTest.php',
	'tx_fileman_domain_model_file' => $extensionClassesPath . 'Domain/Model/File.php',
	'tx_fileman_domain_model_filestorage' => $extensionClassesPath . 'Domain/Model/FileStorage.php',
	'tx_fileman_domain_model_filetest' => $extensionPath . 'Tests/Unit/Domain/Model/FileTest.php',
	'tx_fileman_domain_model_frontenduser' => $extensionClassesPath . 'Domain/Model/FrontendUser.php',
	'tx_fileman_domain_model_frontendusergroup' => $extensionClassesPath . 'Domain/Model/FrontendUserGroup.php',
	'tx_fileman_domain_model_link' => $extensionClassesPath . 'Domain/Model/Link.php',
	'tx_fileman_domain_repository_categoryrepository' => $extensionClassesPath . 'Domain/Repository/CategoryRepository.php',
	'tx_fileman_domain_repository_filerepository' => $extensionClassesPath . 'Domain/Repository/FileRepository.php',
	'tx_fileman_domain_repository_frontendusergrouprepository' => $extensionClassesPath . 'Domain/Repository/FrontendUserGroupRepository.php',
	'tx_fileman_domain_repository_frontenduserrepository' => $extensionClassesPath . 'Domain/Repository/FrontendUserRepository.php',
	'tx_fileman_domain_repository_linkrepository' => $extensionClassesPath . 'Domain/Repository/LinkRepository.php',
	'tx_fileman_domain_validator_filevalidator' => $extensionClassesPath . 'Domain/Validator/FileValidator.php',
	'tx_fileman_domain_validator_linksvalidator' => $extensionClassesPath . 'Domain/Validator/LinksValidator.php',
	'tx_fileman_domain_validator_linkurivalidator' => $extensionClassesPath . 'Domain/Validator/LinkUriValidator.php',
	'tx_fileman_domain_validator_objectpropertiesvalidator' => $extensionClassesPath . 'Domain/Validator/ObjectPropertiesValidator.php',
	'tx_fileman_domain_validator_objectstoragevalidator' => $extensionClassesPath . 'Domain/Validator/ObjectStorageValidator.php',
	'tx_fileman_mvc_controller_actioncontroller' => $extensionClassesPath . 'MVC/Controller/ActionController.php',
	'tx_fileman_mvc_controller_errorondebugcontroller' => $extensionClassesPath . 'MVC/Controller/ErrorOnDebugController.php',
	'tx_fileman_mvc_exception_nopersistrepository' => $extensionClassesPath . 'MVC/Exception/NoPersistRepository.php',
	'tx_fileman_persistence_nopersistrepository' => $extensionClassesPath . 'Persistence/NoPersistRepository.php',
	'tx_fileman_service_fileservice' => $extensionClassesPath . 'Service/FileService.php',
	'tx_fileman_service_userservice' => $extensionClassesPath . 'Service/UserService.php',
	'tx_fileman_service_sortrepositoryservice' => $extensionClassesPath . 'Service/SortRepositoryService.php',
	'tx_fileman_service_typo3csrfprotectservice' => $extensionClassesPath . 'Service/Typo3CsrfProtectService.php',
	'tx_fileman_service_csrfprotectserviceinterface' => $extensionClassesPath . 'Service/CsrfProtectServiceInterface.php',
	'tx_fileman_service_abstractcsrfprotectservice' => $extensionClassesPath . 'Service/AbstractCsrfProtectService.php',
	'tx_fileman_typoscript' => $extensionPath . 'Resources/Private/Scripts/Tx_Fileman_TypoScript.php',
	'tx_fileman_validation_storageerror' => $extensionClassesPath . 'Validation/StorageError.php',
	'tx_fileman_validation_validator_preppedabstractvalidator' => $extensionClassesPath . 'Validation/PreppedAbstractValidator.php',
	'tx_fileman_validation_validatorresolver' => $extensionClassesPath . 'Validation/ValidatorResolver.php',
	'tx_fileman_viewhelpers_file_basenameviewhelper' => $extensionClassesPath . 'ViewHelpers/File/BaseNameViewHelper.php',
	'tx_fileman_viewhelpers_form_errorsviewhelper' => $extensionClassesPath . 'ViewHelpers/Form/ErrorsViewHelper.php',
	'tx_fileman_viewhelpers_form_textareaviewhelper' => $extensionClassesPath . 'ViewHelpers/Form/TextareaViewHelper.php',
	'tx_fileman_viewhelpers_form_textfieldviewhelper' => $extensionClassesPath . 'ViewHelpers/Form/TextfieldViewHelper.php',
	'tx_fileman_viewhelpers_form_uploadviewhelper' => $extensionClassesPath . 'ViewHelpers/Form/UploadViewHelper.php',
	'tx_fileman_viewhelpers_format_camelcaseviewhelper' => $extensionClassesPath . 'ViewHelpers/Format/CamelCaseViewHelper.php',
	'tx_fileman_viewhelpers_format_lowercaseviewhelper' => $extensionClassesPath . 'ViewHelpers/Format/LowerCaseViewHelper.php',
	'tx_fileman_viewhelpers_link_downloadviewhelper' => $extensionClassesPath . 'ViewHelpers/Link/DownloadViewHelper.php',
);
?>