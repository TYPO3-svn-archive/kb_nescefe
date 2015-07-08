<?php
defined ('TYPO3_MODE') or die ('Access denied.');

$ll = 'LLL:EXT:kb_nescefe/Resources/Private/Language/locallang_db.xlf:';

$tempColumns = array(
	'kbnescefe_parentElement' => Array (
		'label' => $ll . 'tt_content.parentElement',
		'config' => Array (
			'type' => 'group',
			'internal_type' => 'db',
			'allowed' => 'tt_content',
			'maxitems' => 1,
			'size' => 1,
		),
	),
	'kbnescefe_parentPosition' => Array (
		'label' => $ll . 'tt_content.parentPosition',
		'config' => Array (
			'type' => 'select',
			'items' => Array (
			),
			'itemsProcFunc' => 'ThinkopenAt\KbNescefe\Hooks\TcaItemsProcessing->contentPositions',
			'default' => '0',
		),
	),
	'kbnescefe_layout' => array(
		'label' => $ll . 'tt_content.layout',
		'config' => Array (
			'type' => 'select',
			'items' => array(
				array($ll . 'tt_content.layout.none', 0),
			),
			'foreign_table' => 'tx_kbnescefe_layout',
			'foreign_table_where' => ' AND tx_kbnescefe_layout.pid=###PAGE_TSCONFIG_ID###',
			'size' => '1',
			'maxitems' => '1',
			'minitems' => '0',
		)
	),
);

if ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kb_nescefe']['templateSoftReference']) {
	$tempColumns['kbnescefe_layout']['config']['softref'] = 'kbnescefe_layout';
}

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('tt_content', $tempColumns, 1);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('tt_content', 'kbnescefe_parentElement, kbnescefe_parentPosition');

// Set fields to be shown/disabled for a kb_nescefe container tt_content element.
$TCA['tt_content']['types']['list']['subtypes_excludelist']['kbnescefe_pi1'] = 'pages,layout,select_key';
$TCA['tt_content']['types']['list']['subtypes_addlist']['kbnescefe_pi1'] = 'kbnescefe_layout';

// Register itemsProcFunc for "tt_content:colPos"
$TCA['tt_content']['columns']['colPos']['config']['origItemsProcFunc'] = $TCA['tt_content']['columns']['colPos']['config']['itemsProcFunc'];
$TCA['tt_content']['columns']['colPos']['config']['itemsProcFunc'] = 'ThinkopenAt\KbNescefe\Hooks\TcaItemsProcessing->colPosHandling';

