<?php
defined ('TYPO3_MODE') or die ('Access denied.');

$ll = 'LLL:EXT:kb_nescefe/Resources/Private/Language/locallang_db.xlf:';

if ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kb_nescefe']['templatesOnPages']) {
	t3lib_extMgm::allowTableOnStandardPages('tx_kbnescefe_layout');
}

return Array (
	'ctrl' => Array (
		'title' => $ll . 'tx_kbnescefe_layout',
		'label' => 'name',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'sortby' => 'sorting',
		'delete' => 'deleted',
		'enablecolumns' => Array(
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'endtime' => 'endtime',
			'fe_group' => 'fe_group',
		),
		'iconfile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('kb_nescefe').'Resources/Public/Images/icon_tx_kbnescefe_layout.gif',
	),
	'feInterface' => Array (
		'fe_admin_fieldList' => 'hidden, starttime, endtime, fe_group, name, fetemplate, betemplate',
	),
	'interface' => Array (
		'showRecordFieldList' => 'hidden,starttime,endtime,fe_group,name,fetemplate,betemplate'
	),
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
			'label' => $ll . 'tx_kbnescefe_layout.name',		
			'config' => Array (
				'type' => 'input',	
				'size' => '30',	
				'eval' => 'required',
			),
		),
		'fetemplate' => Array (		
			'exclude' => 1,		
			'label' => $ll . 'tx_kbnescefe_layout.fetemplate',		
			'config' => Array (
				'type' => 'input',	
				'size' => '50',	
				'eval' => 'required',
				'default' => 'EXT:kb_nescefe/Resources/Private/Templates/Frontend/TwoColumns.html',
			),
		),
		'betemplate' => Array (		
			'exclude' => 1,		
			'label' => $ll . 'tx_kbnescefe_layout.betemplate',		
			'config' => Array (
				'type' => 'input',	
				'size' => '50',	
				'eval' => 'required',
				'default' => 'EXT:kb_nescefe/Resources/Private/Templates/Backend/TwoColumns.html',
			),
		),
	),
	'types' => Array (
		'0' => Array('showitem' => 'hidden;;1;;1-1-1, name;;;;2-2-2, fetemplate, betemplate'),
	),
);

