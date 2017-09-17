<?php
defined('TYPO3_MODE') or die();

#@TODO what about FAL?

// add tca csh
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr(
	'tx_fileman_domain_model_file',
	'EXT:fileman/Resources/Private/Language/locallang_csh_tx_fileman_domain_model_file.xml'
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr(
	'tx_fileman_domain_model_category',
	'EXT:fileman/Resources/Private/Language/locallang_csh_tx_fileman_domain_model_category.xml'
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr(
	'tx_fileman_domain_model_link',
	'EXT:fileman/Resources/Private/Language/locallang_csh_tx_fileman_domain_model_link.xml'
);

// allow creation of records on normal pages
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_fileman_domain_model_file');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_fileman_domain_model_category');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_fileman_domain_model_link');