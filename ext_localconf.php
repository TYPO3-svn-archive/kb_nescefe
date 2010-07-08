<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');


$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['moveRecordClass']['kb_nescefe_pi1'] = 'EXT:kb_nescefe/class.tx_kbnescefe_t3libtcemain.php:tx_kbnescefe_t3libtcemain';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass']['kb_nescefe_pi1'] = 'EXT:kb_nescefe/class.tx_kbnescefe_t3libtcemain.php:tx_kbnescefe_t3libtcemain';
// $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['kb_nescefe_pi1'] = 'EXT:kb_nescefe/class.tx_kbnescefe_t3libtcemain.php:tx_kbnescefe_t3libtcemain';


$_EXTCONF = unserialize($_EXTCONF);    // unserializing the configuration so we can use it here
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY]['copyRecursive'] = intval($_EXTCONF['copyRecursive']) ? true : false;
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY]['templatesOnPages'] = intval($_EXTCONF['templatesOnPages']) ? true : false;
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY]['templateSoftReference'] = intval($_EXTCONF['templateSoftReference']) ? true : false;
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY]['containerElementColPos'] = intval($_EXTCONF['containerElementColPos']) ? intval($_EXTCONF['containerElementColPos']) : 10;
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY]['beStyles'] = trim($_EXTCONF['beStyles']);

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['list_type_Info'][$_EXTKEY.'_pi1'][] = 'EXT:kb_nescefe/class.tx_kbnescefe_contentPreview.php:tx_kbnescefe_contentPreview->renderPluginPreview';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['tt_content_drawHeader'][] = 'EXT:kb_nescefe/class.tx_kbnescefe_layout.php:tx_kbnescefe_layout->drawHeader';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['tt_content_drawColHeader'][] = 'EXT:kb_nescefe/class.tx_kbnescefe_layout.php:tx_kbnescefe_layout->drawColHeader';

$GLOBALS['TYPO3_CONF_VARS']['BE']['XCLASS']['t3lib/class.t3lib_clipboard.php'] = t3lib_extMgm::extPath($_EXTKEY).'class.ux_t3lib_clipboard.php';
$GLOBALS['TYPO3_CONF_VARS']['BE']['XCLASS']['ext/cms/layout/class.tx_cms_layout.php'] = t3lib_extMgm::extPath($_EXTKEY).'class.ux_tx_cms_layout.php';

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['softRefParser']['kb_nescefe_parent'] = 'EXT:kb_nescefe/class.tx_kbnescefe_softrefs.php:&tx_kbnescefe_softrefs';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['softRefParser']['kb_nescefe_column'] = 'EXT:kb_nescefe/class.tx_kbnescefe_softrefs.php:&tx_kbnescefe_softrefs';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['softRefParser']['kb_nescefe_container'] = 'EXT:kb_nescefe/class.tx_kbnescefe_softrefs.php:&tx_kbnescefe_softrefs';

t3lib_extMgm::addPItoST43($_EXTKEY, 'pi1/class.tx_kbnescefe_pi1.php', '_pi1', 'list_type', 1);

$TCA['tt_content']['columns']['colPos']['config']['items']['kb_nescefe'] = Array('LLL:EXT:kb_nescefe/locallang_db.xml:tt_content.containerColumn', $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY]['containerElementColPos']);

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/impexp/class.tx_impexp.php']['before_writeRecordsRecords']['kb_nescefe'] = 'EXT:kb_nescefe/class.tx_kbnescefe_impexpHook.php:tx_kbnescefe_impexpHook->updateColPos';

?>
