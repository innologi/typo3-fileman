<?php
defined('TYPO3_MODE') or die();

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'Innologi.' . $_EXTKEY,
	'Filelist',
	array(
		'Category' => 'list, sort, new, create, edit, update, delete',
		'File' => 'list, sort, download, new, create, edit, update, delete, search',
		'Link' => 'new, create, edit, update, delete',
	),
	// non-cacheable actions
	array(
		// we don't cache new because feUser-id might be used in token generation
		'Category' => 'new, sort, create, update, delete',
		// we don't cache list because of the category owner permissions (quick workaround)
		'File' => 'list, sort, download, create, update, delete, search',
		'Link' => 'create, update, delete',
	)
);
