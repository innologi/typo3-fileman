<?php
defined('TYPO3_MODE') or die();

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
				3,
				4,
			)
		) ? ', list' : '';
	}
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'Innologi.' . $_EXTKEY,
	'Filelist',
	array(
		'Category' => 'list, sort, new, create, edit, update, delete, ajaxVerifyToken, ajaxGenerateTokens',
		'File' => 'list, sort, download, new, create, edit, update, delete, search',
		'Link' => 'new, create, edit, update, delete',
	),
	// non-cacheable actions
	array(
		'Category' => 'sort, create, update, delete, ajaxVerifyToken, ajaxGenerateTokens' . $noCache,
		// we don't cache list because of the category owner permissions (quick workaround)
		'File' => 'list, sort, download, create, update, delete, search' . $noCache,
		'Link' => 'create, update, delete',
	)
);
