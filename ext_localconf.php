<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

// STRONG csrf protection levels prevent caching of some views
$noCache = '';
if (isset($TYPO3_CONF_VARS['EXT']['extConf'][$_EXTKEY])) {
	$extConf = unserialize($TYPO3_CONF_VARS['EXT']['extConf'][$_EXTKEY]);
	if (isset($extConf['csrf_protection_level'])) {
		$noCache = in_array(
			(int)$extConf['csrf_protection_level'],
			array(
				// using ext-constants in this file produces problems when the extension
				// is uninstalled but the cache isn't cleared yet
				3, //Tx_Fileman_Service_CsrfProtectServiceInterface::STRONG,
				4, //Tx_Fileman_Service_CsrfProtectServiceInterface::STRONG_PLUS
			)
		) ? ', list' : '';
	}
}

Tx_Extbase_Utility_Extension::configurePlugin(
	$_EXTKEY,
	'Filelist',
	array(
		'Category' => 'list, new, create, edit, update, delete, ajaxVerifyToken, ajaxGenerateTokens',
		'File' => 'list, download, new, create, edit, update, delete',
		'Link' => 'new, create, edit, update, delete',
	),
	// non-cacheable actions
	array(
		'Category' => 'create, update, delete, ajaxVerifyToken, ajaxGenerateTokens' . $noCache,
		'File' => 'download, create, update, delete' . $noCache,
		'Link' => 'create, update, delete',
	)
);

Tx_Extbase_Utility_Extension::configurePlugin(
	$_EXTKEY,
	'Search',
	array(
		'File' => 'search, searchResult',
	),
	// non-cacheable actions
	array(
		'File' => 'searchResult',
	)
);
