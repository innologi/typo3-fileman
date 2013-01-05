<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

Tx_Extbase_Utility_Extension::configurePlugin(
	$_EXTKEY,
	'Filelist',
	array(
		'Category' => 'list, new, create, edit, update, delete',
		'File' => 'list, download, new, create, edit, update, delete',
		'Link' => 'new, create, edit, update, delete',

	),
	// non-cacheable actions
	array(
		'Category' => 'create, update, delete',
		'File' => 'download, create, update, delete',
		'Link' => 'create, update, delete',

	)
);

?>