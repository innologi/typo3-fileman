<?php
defined('TYPO3_MODE') or die();

return [
	'ctrl' => [
		'title'	=> 'LLL:EXT:fileman/Resources/Private/Language/locallang_db.xml:tx_fileman_domain_model_category',
		'label' => 'title',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'dividers2tabs' => TRUE,
		'sortby' => 'sorting',
		'versioningWS' => TRUE,
		'origUid' => 't3_origuid',
		'languageField' => 'sys_language_uid',
		'transOrigPointerField' => 'l10n_parent',
		'transOrigDiffSourceField' => 'l10n_diffsource',
		'delete' => 'deleted',
		'enablecolumns' => [
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'endtime' => 'endtime',
			'fe_group' => 'fe_group'
		],
		'searchFields' => 'title,description,',
		'iconfile' => 'EXT:fileman/Resources/Public/Icons/tx_fileman_domain_model_category.gif'
	],
	'interface' => [
		'showRecordFieldList' => 'sys_language_uid, l10n_parent, l10n_diffsource, hidden, title, description, sub_category, file, link, parent_category, fe_user',
	],
	'types' => [
		'0' => ['showitem' => 'sys_language_uid, l10n_parent, l10n_diffsource, hidden, title, description,parent_category,fe_user,
			--div--;LLL:EXT:fileman/Resources/Private/Language/locallang_be.xml:tca_tab_content,sub_category,file,link,
			--div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.access,starttime,endtime,fe_group'
		],
	],
	'palettes' => [],
	'columns' => [
		'sys_language_uid' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:lang/Resources/Private/Language/locallang_general.xlf:LGL.language',
			'config' => [
				'type' => 'select',
				'renderType' => 'selectSingle',
				'foreign_table' => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => [
					['LLL:EXT:lang/Resources/Private/Language/locallang_general.xlf:LGL.allLanguages', -1],
					['LLL:EXT:lang/Resources/Private/Language/locallang_general.xlf:LGL.default_value', 0]
				],
				'default' => 0,
				'fieldWizard' => [
					'selectIcons' => [
						'disabled' => FALSE,
					],
				],
			]
		],
		'l10n_parent' => [
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude' => TRUE,
			'label' => 'LLL:EXT:lang/Resources/Private/Language/locallang_general.xlf:LGL.l18n_parent',
			'config' => [
				'type' => 'select',
				'renderType' => 'selectSingle',
				'items' => [
					['', 0]
				],
				'foreign_table' => 'sys_category',
				'foreign_table_where' => 'AND sys_category.uid=###REC_FIELD_l10n_parent### AND sys_category.sys_language_uid IN (-1,0)',
				'default' => 0
			]
		],
		'l10n_diffsource' => [
			'config' => [
				'type' => 'passthrough',
				'default' => ''
			]
		],
		't3ver_label' => [
			'label' => 'LLL:EXT:lang/Resources/Private/Language/locallang_general.xlf:LGL.versionLabel',
			'config' => [
				'type' => 'input',
				'size' => 30,
				'max' => 255,
			]
		],
		'hidden' => [
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/Resources/Private/Language/locallang_general.xlf:LGL.hidden',
			'config' => [
				'type' => 'check',
			],
		],
		'starttime' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:lang/Resources/Private/Language/locallang_general.xlf:LGL.starttime',
			'config' => [
				'type' => 'input',
				'renderType' => 'inputDateTime',
				'eval' => 'datetime',
				'default' => 0,
				'behaviour' => [
					'allowLanguageSynchronization' => TRUE,
				]
			]
		],
		'endtime' => [
			'exclude' => TRUE,
			'label' => 'LLL:EXT:lang/Resources/Private/Language/locallang_general.xlf:LGL.endtime',
			'config' => [
				'type' => 'input',
				'renderType' => 'inputDateTime',
				'eval' => 'datetime',
				'default' => 0,
				'range' => [
					'upper' => mktime(0, 0, 0, 1, 1, 2038),
				],
				'behaviour' => [
					'allowLanguageSynchronization' => TRUE,
				]
			]
		],
		'fe_group' => [
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/Resources/Private/Language/locallang_general.xlf:LGL.fe_group',
			'config' => [
				'type' => 'select',
				'renderType' => 'selectMultipleSideBySide',
				'size' => 7,
				'maxitems' => 20,
				'items' => [
					[
						'LLL:EXT:lang/Resources/Private/Language/locallang_general.xlf:LGL.hide_at_login',
						-1
					],
					[
						'LLL:EXT:lang/Resources/Private/Language/locallang_general.xlf:LGL.any_login',
						-2
					],
					[
						'LLL:EXT:lang/Resources/Private/Language/locallang_general.xlf:LGL.usergroups',
						'--div--'
					]
				],
				'exclusiveKeys' => '-1,-2',
				'foreign_table' => 'fe_groups',
				'foreign_table_where' => 'ORDER BY fe_groups.title'
			]
		],
		'title' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:fileman/Resources/Private/Language/locallang_db.xml:tx_fileman_domain_model_category.title',
			'config' => [
				'type' => 'input',
				'size' => 48,
				'eval' => 'trim,required'
			],
		],
		'description' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:fileman/Resources/Private/Language/locallang_db.xml:tx_fileman_domain_model_category.description',
			'config' => [
				'type' => 'text',
				'cols' => 48,
				'rows' => 20,
				'eval' => 'trim',
				'enableRichtext' => TRUE
			],
		],
		'sub_category' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:fileman/Resources/Private/Language/locallang_db.xml:tx_fileman_domain_model_category.sub_category',
			'config' => [
				'type' => 'select',
				'renderType' => 'selectMultipleSideBySide',
				'foreign_table' => 'tx_fileman_domain_model_category',
				'MM' => 'tx_fileman_category_mm',
				'MM_opposite_field' => 'parent_category', //this needs to be set on only ONE side, with bidirectional!
				'size' => 10,
				'autoSizeMax' => 30,
				'maxitems' => 999,
				'multiple' => 0,
				'fieldControl' => [
					'editPopup' => [
						'disabled' => FALSE,
					],
					'addRecord' => [
						'disabled' => FALSE,
					],
				],
			],
		],
		'file' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:fileman/Resources/Private/Language/locallang_db.xml:tx_fileman_domain_model_category.file',
			'config' => [
				'type' => 'select',
				'renderType' => 'selectMultipleSideBySide',
				'foreign_table' => 'tx_fileman_domain_model_file',
				'MM' => 'tx_fileman_file_category_mm',
				'MM_opposite_field' => 'category', //this needs to be set on only ONE side, with bidirectional!
				'size' => 10,
				'autoSizeMax' => 30,
				'maxitems' => 999,
				'multiple' => 0,
				'fieldControl' => [
					'editPopup' => [
						'disabled' => FALSE,
					],
					'addRecord' => [
						'disabled' => FALSE,
					],
				],
			],
		],
		'link' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:fileman/Resources/Private/Language/locallang_db.xml:tx_fileman_domain_model_category.link',
			'config' => [
				'type' => 'select',
				'renderType' => 'selectMultipleSideBySide',
				'foreign_table' => 'tx_fileman_domain_model_link',
				'MM' => 'tx_fileman_link_category_mm',
				'MM_opposite_field' => 'category',
				'size' => 10,
				'autoSizeMax' => 30,
				'maxitems' => 999,
				'multiple' => 0,
				'fieldControl' => [
					'editPopup' => [
						'disabled' => FALSE,
					],
					'addRecord' => [
						'disabled' => FALSE,
					],
				],
			],
		],
		'parent_category' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:fileman/Resources/Private/Language/locallang_db.xml:tx_fileman_domain_model_category.parent_category',
			'config' => [
				'type' => 'select',
				'renderType' => 'selectMultipleSideBySide',
				'foreign_table' => 'tx_fileman_domain_model_category',
				'MM' => 'tx_fileman_category_mm',
				'size' => 10,
				'autoSizeMax' => 30,
				'maxitems' => 999,
				'multiple' => 0,
				'fieldControl' => [
					'editPopup' => [
						'disabled' => FALSE,
					],
					'addRecord' => [
						'disabled' => FALSE,
					],
				],
			],
		],
		'fe_user' => [
			'exclude' => 0,
			'label' => 'LLL:EXT:fileman/Resources/Private/Language/locallang_db.xml:tx_fileman_domain_model_category.fe_user',
			'config' => [
				'type' => 'select',
				'renderType' => 'selectSingle',
				'foreign_table' => 'fe_users',
				'minitems' => 0,
				'maxitems' => 1,
			],
		],
	],
];