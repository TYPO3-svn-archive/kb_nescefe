<?php
defined ('TYPO3_MODE') or die ('Access denied.');


\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
	$_EXTKEY,
	'Pi1',
	'LLL:EXT:kb_nescefe/Resources/Private/Language/locallang_db.xlf:tt_content.CType_pi1',
	'EXT:kb_nescefe/ext_icon.gif'
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('
mod.wizards.newContentElement.wizardItems.common.show := addToList(kbnescefe_pi1);
');

if (TYPO3_MODE == 'BE') {
	$wizard = 'ThinkopenAt\KbNescefe\Hooks\NewContentElementWizard';
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms']['db_new_content_el']['wizardItemsHook']['kb_nescefe'] = $wizard;
	/* ------------- HOOKS: Click menu --------------- begin --------------- */
	$GLOBALS['TBE_MODULES_EXT']['xMOD_alt_clickmenu']['extendCMclasses']['kb_nescefe'] = array(
		'name' => 'ThinkopenAt\KbNescefe\Hooks\ClickMenu'
	);
	/* ------------- HOOKS: Click menu --------------- end ----------------- */
}


