<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');


$TCA['tx_kbnescefe_containers'] = Array (
	'ctrl' => $TCA['tx_kbnescefe_containers']['ctrl'],
	'interface' => Array (
		'showRecordFieldList' => 'hidden,starttime,endtime,fe_group,name,fetemplate,betemplate'
	),
	'feInterface' => $TCA['tx_kbnescefe_containers']['feInterface'],
	'columns' => Array (
		'hidden' => Array (		
			'exclude' => 1,	
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.hidden',
			'config' => Array (
				'type' => 'check',
				'default' => '0'
			)
		),
		'starttime' => Array (		
			'exclude' => 1,	
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.starttime',
			'config' => Array (
				'type' => 'input',
				'size' => '8',
				'max' => '20',
				'eval' => 'date',
				'default' => '0',
				'checkbox' => '0'
			)
		),
		'endtime' => Array (		
			'exclude' => 1,	
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.endtime',
			'config' => Array (
				'type' => 'input',
				'size' => '8',
				'max' => '20',
				'eval' => 'date',
				'checkbox' => '0',
				'default' => '0',
				'range' => Array (
					'upper' => mktime(0,0,0,12,31,2020),
					'lower' => mktime(0,0,0,date('m')-1,date('d'),date('Y'))
				)
			)
		),
		'fe_group' => Array (		
			'exclude' => 1,	
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.fe_group',
			'config' => Array (
				'type' => 'select',	
				'items' => Array (
					Array('', 0),
					Array('LLL:EXT:lang/locallang_general.php:LGL.hide_at_login', -1),
					Array('LLL:EXT:lang/locallang_general.php:LGL.any_login', -2),
					Array('LLL:EXT:lang/locallang_general.php:LGL.usergroups', '--div--')
				),
				'foreign_table' => 'fe_groups'
			)
		),
		'name' => Array (		
			'exclude' => 1,		
			'label' => 'LLL:EXT:kb_nescefe/locallang_db.xml:tx_kbnescefe_containers.name',		
			'config' => Array (
				'type' => 'input',	
				'size' => '30',	
				'eval' => 'required',
			),
		),
		'fetemplate' => Array (		
			'exclude' => 1,		
			'label' => 'LLL:EXT:kb_nescefe/locallang_db.xml:tx_kbnescefe_containers.fetemplate',		
			'config' => Array (
				'type' => 'input',	
				'size' => '30',	
				'eval' => 'required',
				'default' => 'EXT:kb_nescefe/res/horiz_cols.html',
			),
		),
		'betemplate' => Array (		
			'exclude' => 1,		
			'label' => 'LLL:EXT:kb_nescefe/locallang_db.xml:tx_kbnescefe_containers.betemplate',		
			'config' => Array (
				'type' => 'input',	
				'size' => '30',	
				'eval' => 'required',
				'default' => 'EXT:kb_nescefe/res/be_horiz_cols.html',
			),
		),
	),
	'types' => Array (
		'0' => Array('showitem' => 'hidden;;1;;1-1-1, name;;;;2-2-2, fetemplate, betemplate'),
	),
);


?>
