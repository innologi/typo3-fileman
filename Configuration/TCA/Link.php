<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

$TCA['tx_fileman_domain_model_link'] = array(
	'ctrl' => $TCA['tx_fileman_domain_model_link']['ctrl'],
	'interface' => array(
		'showRecordFieldList' => 'sys_language_uid, l10n_parent, l10n_diffsource, hidden, link_uri, link_name, description, category, fe_user',
	),
	'types' => array(
		'1' => array('showitem' => 'sys_language_uid;;;;1-1-1, l10n_parent, l10n_diffsource, hidden;;1, link_uri, link_name,description,category, fe_user,--div--;LLL:EXT:cms/locallang_ttc.xml:tabs.access,starttime, endtime'),
	),
	'palettes' => array(
		'1' => array('showitem' => ''),
	),
	'columns' => array(
		'sys_language_uid' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.language',
			'config' => array(
				'type' => 'select',
				'foreign_table' => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => array(
					array('LLL:EXT:lang/locallang_general.xml:LGL.allLanguages', -1),
					array('LLL:EXT:lang/locallang_general.xml:LGL.default_value', 0)
				),
			),
		),
		'l10n_parent' => array(
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.l18n_parent',
			'config' => array(
				'type' => 'select',
				'items' => array(
					array('', 0),
				),
				'foreign_table' => 'tx_fileman_domain_model_link',
				'foreign_table_where' => 'AND tx_fileman_domain_model_link.pid=###CURRENT_PID### AND tx_fileman_domain_model_link.sys_language_uid IN (-1,0)',
			),
		),
		'l10n_diffsource' => array(
			'config' => array(
				'type' => 'passthrough',
			),
		),
		't3ver_label' => array(
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.versionLabel',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'max' => 255,
			)
		),
		'hidden' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config' => array(
				'type' => 'check',
			),
		),
		'starttime' => array(
			'exclude' => 1,
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.starttime',
			'config' => array(
				'type' => 'input',
				'size' => 13,
				'max' => 20,
				'eval' => 'datetime',
				'checkbox' => 0,
				'default' => 0,
				'range' => array(
					'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y'))
				),
			),
		),
		'endtime' => array(
			'exclude' => 1,
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.endtime',
			'config' => array(
				'type' => 'input',
				'size' => 13,
				'max' => 20,
				'eval' => 'datetime',
				'checkbox' => 0,
				'default' => 0,
				'range' => array(
					'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y'))
				),
			),
		),
		'link_uri' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:fileman/Resources/Private/Language/locallang_db.xml:tx_fileman_domain_model_link.link_uri',
			'config' => array(
				'type' => 'input',
				'size' => 48,
				'eval' => 'trim,required'
			),
		),
		'link_name' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:fileman/Resources/Private/Language/locallang_db.xml:tx_fileman_domain_model_link.link_name',
			'config' => array(
				'type' => 'input',
				'size' => 48,
				'eval' => 'trim,required'
			),
		),
		'description' => array(
				'exclude' => 0,
				'label' => 'LLL:EXT:fileman/Resources/Private/Language/locallang_db.xml:tx_fileman_domain_model_link.description',
				'config' => array(
						'type' => 'text',
						'cols' => 48,
						'rows' => 20,
						'eval' => 'trim',
						'wizards' => array(
								'RTE' => array(
										'icon' => 'wizard_rte2.gif',
										'notNewRecords'=> 1,
										'RTEonly' => 1,
										'script' => 'wizard_rte.php',
										'title' => 'LLL:EXT:cms/locallang_ttc.xml:bodytext.W.RTE',
										'type' => 'script'
								)
						)
				),
				'defaultExtras' => 'richtext[]',
		),
		'category' => array(
				'exclude' => 0,
				'label' => 'LLL:EXT:fileman/Resources/Private/Language/locallang_db.xml:tx_fileman_domain_model_link.category',
				'config' => array(
						'type' => 'select',
						'foreign_table' => 'tx_fileman_domain_model_category',
						'MM' => 'tx_fileman_link_category_mm',
						'size' => 10,
						'autoSizeMax' => 30,
						'maxitems' => 999,
						'multiple' => 0,
						'wizards' => array(
								'_PADDING' => 1,
								'_VERTICAL' => 1,
								'edit' => array(
										'type' => 'popup',
										'title' => 'Edit',
										'script' => 'wizard_edit.php',
										'icon' => 'edit2.gif',
										'popup_onlyOpenIfSelected' => 1,
										'JSopenParams' => 'height=350,width=580,status=0,menubar=0,scrollbars=1',
								),
								'add' => Array(
										'type' => 'script',
										'title' => 'Create new',
										'icon' => 'add.gif',
										'params' => array(
												'table' => 'tx_fileman_domain_model_category',
												'pid' => '###CURRENT_PID###',
												'setValue' => 'prepend'
										),
										'script' => 'wizard_add.php',
								),
						),
				),
		),
		'fe_user' => array(
				'exclude' => 0,
				'label' => 'LLL:EXT:fileman/Resources/Private/Language/locallang_db.xml:tx_fileman_domain_model_link.fe_user',
				'config' => array(
						'type' => 'select',
						'foreign_table' => 'fe_users',
						'minitems' => 0,
						'maxitems' => 1,
				),
		),
	),
);

?>