<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
	$_EXTKEY,
	'Filelist',
	'File Manager: List'
);

$pluginSignature = str_replace('_','',$_EXTKEY) . '_filelist';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($pluginSignature, 'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForms/flexform_filelist.xml');

#@TODO documenteer pageTS TCEMAIN.clearCacheCmd = pid
#@TODO namespaces?
#@TODO what about FAL?
#@TODO locallang_csh_tx_fileman_domain_model_link.xml

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'File Manager');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_fileman_domain_model_file', 'EXT:fileman/Resources/Private/Language/locallang_csh_tx_fileman_domain_model_file.xml');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_fileman_domain_model_file');
$TCA['tx_fileman_domain_model_file'] = array(
	'ctrl' => array(
		'title'	=> 'LLL:EXT:fileman/Resources/Private/Language/locallang_db.xml:tx_fileman_domain_model_file',
		'label' => 'alternate_title',
		'label_alt' => 'file_uri',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'dividers2tabs' => TRUE,
		'sortby' => 'sorting',
		'versioningWS' => 2,
		'versioning_followPages' => TRUE,
		'origUid' => 't3_origuid',
		'languageField' => 'sys_language_uid',
		'transOrigPointerField' => 'l10n_parent',
		'transOrigDiffSourceField' => 'l10n_diffsource',
		'delete' => 'deleted',
		'enablecolumns' => array(
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'endtime' => 'endtime',
			'fe_group' => 'fe_group'
		),
		'searchFields' => 'filename,file_uri,alternate_title,description,category,links,',
		'dynamicConfigFile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'Configuration/TCA/File.php',
		'iconfile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($_EXTKEY) . 'Resources/Public/Icons/tx_fileman_domain_model_file.gif'
	),
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_fileman_domain_model_category', 'EXT:fileman/Resources/Private/Language/locallang_csh_tx_fileman_domain_model_category.xml');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_fileman_domain_model_category');
$TCA['tx_fileman_domain_model_category'] = array(
	'ctrl' => array(
		'title'	=> 'LLL:EXT:fileman/Resources/Private/Language/locallang_db.xml:tx_fileman_domain_model_category',
		'label' => 'title',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'dividers2tabs' => TRUE,
		'sortby' => 'sorting',
		'versioningWS' => 2,
		'versioning_followPages' => TRUE,
		'origUid' => 't3_origuid',
		'languageField' => 'sys_language_uid',
		'transOrigPointerField' => 'l10n_parent',
		'transOrigDiffSourceField' => 'l10n_diffsource',
		'delete' => 'deleted',
		'enablecolumns' => array(
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'endtime' => 'endtime',
			'fe_group' => 'fe_group'
		),
		'searchFields' => 'title,description,',
		'dynamicConfigFile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'Configuration/TCA/Category.php',
		'iconfile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($_EXTKEY) . 'Resources/Public/Icons/tx_fileman_domain_model_category.gif'
	),
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_fileman_domain_model_link', 'EXT:fileman/Resources/Private/Language/locallang_csh_tx_fileman_domain_model_link.xml');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_fileman_domain_model_link');
$TCA['tx_fileman_domain_model_link'] = array(
	'ctrl' => array(
		'title'	=> 'LLL:EXT:fileman/Resources/Private/Language/locallang_db.xml:tx_fileman_domain_model_link',
		'label' => 'link_name',
		'label_alt' => 'link_uri',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'dividers2tabs' => TRUE,
		'sortby' => 'sorting',
		'versioningWS' => 2,
		'versioning_followPages' => TRUE,
		'origUid' => 't3_origuid',
		'languageField' => 'sys_language_uid',
		'transOrigPointerField' => 'l10n_parent',
		'transOrigDiffSourceField' => 'l10n_diffsource',
		'delete' => 'deleted',
		'enablecolumns' => array(
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'endtime' => 'endtime',
			'fe_group' => 'fe_group'
		),
		'searchFields' => 'link_uri,link_name,description,',
		'dynamicConfigFile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'Configuration/TCA/Link.php',
		'iconfile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($_EXTKEY) . 'Resources/Public/Icons/tx_fileman_domain_model_link.gif'
	),
);

?>