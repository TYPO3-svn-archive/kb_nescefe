<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

define('PATH_kb_nescefe', t3lib_extMgm::extPath($_EXTKEY));

$tempColumns = array(
	'parentPosition' => Array (
		'label' => 'LLL:EXT:kb_nescefe/locallang_db.xml:tt_content.cPos',
		'config' => Array (
			'type' => 'select',
			'items' => Array (
			),
			'itemsProcFunc' => 'EXT:kb_nescefe/class.tx_kbnescefe_itemproc.php:tx_kbnescefe_itemproc->contentPositions',
			'default' => '0',
			'softref' => 'kb_nescefe_parent',
		),
	),
	'container' => array(
		'label' => 'LLL:EXT:kb_nescefe/locallang_db.xml:tt_content.container',
		'config' => Array (
			'type' => 'select',
			'items' => array(
				array('LLL:EXT:kb_nescefe/locallang_db.xml:tt_content.container.none', 0),
			),
			'foreign_table' => 'tx_kbnescefe_containers',
			'foreign_table_where' => ' AND tx_kbnescefe_containers.pid=###PAGE_TSCONFIG_ID###',
			'size' => '1',
			'maxitems' => '1',
			'minitems' => '0',
			'softref' => 'kb_nescefe_container',
		)
	),
);
			
if ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY]['templateSoftReference']) {
	$tempColumns['container']['config']['softref'] = 'kb_nescefe_container';
}

$TCA['tx_kbnescefe_containers'] = Array (
	'ctrl' => Array (
		'title' => 'LLL:EXT:kb_nescefe/locallang_db.xml:tx_kbnescefe_containers',		
		'label' => 'name',	
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'sortby' => 'sorting',	
		'delete' => 'deleted',	
		'enablecolumns' => Array (		
			'disabled' => 'hidden',	
			'starttime' => 'starttime',	
			'endtime' => 'endtime',	
			'fe_group' => 'fe_group',
		),
		'dynamicConfigFile' => PATH_kb_nescefe.'tca.php',
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_kbnescefe_containers.gif',
	),
	'feInterface' => Array (
		'fe_admin_fieldList' => 'hidden, starttime, endtime, fe_group, name, fetemplate, betemplate',
	)
);



t3lib_div::loadTCA('tt_content');
t3lib_extMgm::addTCAcolumns('tt_content', $tempColumns, 1);
t3lib_extMgm::addToAllTCAtypes('tt_content', 'parentPosition');

$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1'] = 'pages,layout,select_key';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1'] = 'container';
$TCA['tt_content']['columns']['colPos']['config']['items']['kb_nescefe'] = Array('LLL:EXT:kb_nescefe/locallang_db.xml:tt_content.containerColumn', $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY]['containerElementColPos']);

t3lib_extMgm::addPlugin(Array('LLL:EXT:kb_nescefe/locallang_db.xml:tt_content.CType_pi1', $_EXTKEY.'_pi1', 'EXT:kb_nescefe/ext_icon.gif'), 'list_type');

t3lib_extMgm::addPageTSConfig('
mod.wizards.newContentElement.wizardItems.common.show := addToList(kb_nescefe_pi1);
');

$GLOBALS['TBE_MODULES_EXT']['xMOD_db_new_content_el']['addElClasses']['tx_kbnescefe_dbNewContentEl'] = PATH_kb_nescefe.'class.tx_kbnescefe_dbNewContentEl.php';

if ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY]['templatesOnPages']) {
	t3lib_extMgm::allowTableOnStandardPages('tx_kbnescefe_containers');
}

?>
