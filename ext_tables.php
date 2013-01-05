<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

Tx_Extbase_Utility_Extension::registerPlugin(
	$_EXTKEY,
	'Filelist',
	'File Manager: List'
);

$pluginSignature = str_replace('_','',$_EXTKEY) . '_' . filelist;
$TCA['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
t3lib_extMgm::addPiFlexFormValue($pluginSignature, 'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForms/flexform_filelist.xml');

#@TODO documenteer pageTS TCEMAIN.clearCacheCmd = pid

#@TODO locallang_csh_tx_fileman_domain_model_link.xml

t3lib_extMgm::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'File Manager');

t3lib_extMgm::addLLrefForTCAdescr('tx_fileman_domain_model_file', 'EXT:fileman/Resources/Private/Language/locallang_csh_tx_fileman_domain_model_file.xml');
t3lib_extMgm::allowTableOnStandardPages('tx_fileman_domain_model_file');
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
		),
		'searchFields' => 'filename,file_uri,alternate_title,description,category,links,',
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY) . 'Configuration/TCA/File.php',
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY) . 'Resources/Public/Icons/tx_fileman_domain_model_file.gif'
	),
);

t3lib_extMgm::addLLrefForTCAdescr('tx_fileman_domain_model_category', 'EXT:fileman/Resources/Private/Language/locallang_csh_tx_fileman_domain_model_category.xml');
t3lib_extMgm::allowTableOnStandardPages('tx_fileman_domain_model_category');
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
		),
		'searchFields' => 'title,description,',
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY) . 'Configuration/TCA/Category.php',
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY) . 'Resources/Public/Icons/tx_fileman_domain_model_category.gif'
	),
);

t3lib_extMgm::addLLrefForTCAdescr('tx_fileman_domain_model_link', 'EXT:fileman/Resources/Private/Language/locallang_csh_tx_fileman_domain_model_link.xml');
t3lib_extMgm::allowTableOnStandardPages('tx_fileman_domain_model_link');
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
		),
		'searchFields' => 'link_uri,link_name,description,',
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY) . 'Configuration/TCA/Link.php',
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY) . 'Resources/Public/Icons/tx_fileman_domain_model_link.gif'
	),
);

?>