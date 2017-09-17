<?php
defined('TYPO3_MODE') or die();

// add the flexform
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
	'fileman_filelist',
	'FILE:EXT:fileman/Configuration/FlexForms/flexform_filelist.xml'
);
# @CGL do we still need this?
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['fileman_filelist'] = 'pi_flexform';

// register the plugin
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
	'Innologi.Fileman',
	'Filelist',
	'File Manager: List'
);