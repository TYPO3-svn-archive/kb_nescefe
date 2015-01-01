<?php
defined ('TYPO3_MODE') or die ('Access denied.');

/* ------------- Extension configuration settings --------------- begin --------------- */
$_EXTCONF = unserialize($_EXTCONF);    // unserializing the configuration so we can use it here
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY]['copyRecursive'] = intval($_EXTCONF['copyRecursive']) ? TRUE : FALSE;
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY]['localizeRecursive'] = intval($_EXTCONF['localizeRecursive']) ? TRUE : FALSE;
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY]['templatesOnPages'] = intval($_EXTCONF['templatesOnPages']) ? TRUE : FALSE;
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY]['templateSoftReference'] = intval($_EXTCONF['templateSoftReference']) ? TRUE : FALSE;
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY]['containerElementColPos'] = intval($_EXTCONF['containerElementColPos']) ? intval($_EXTCONF['containerElementColPos']) : 10;
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$_EXTKEY]['disableElementContentOL'] = intval($_EXTCONF['disableElementContentOL']) ? TRUE : FALSE;
/* ------------- Extension configuration settings --------------- end ----------------- */



/* ------------- HOOKS: DataHandler --------------- begin --------------- */
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['moveRecordClass']['kbnescefe_pi1'] = 'ThinkopenAt\KbNescefe\Hooks\DataHandler';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass']['kbnescefe_pi1'] = 'ThinkopenAt\KbNescefe\Hooks\DataHandler';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['kbnescefe_pi1'] = 'ThinkopenAt\KbNescefe\Hooks\DataHandler';
/* ------------- HOOKS: DataHandler --------------- end ----------------- */


/* ------------- HOOKS: Page module --------------- begin --------------- */
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['list_type_Info']['kbnescefe_pi1'][] = 'ThinkopenAt\KbNescefe\Hooks\ContentPreview->renderPluginPreview';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['typo3/class.db_list.inc']['makeQueryArray']['kb_nescefe'] = 'ThinkopenAt\KbNescefe\Hooks\PageLayoutView';
/* ------------- HOOKS: Page module --------------- end ----------------- */


/* ------------- HOOKS: Import/Export --------------- begin --------------- */
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/impexp/class.tx_impexp.php']['before_writeRecordsRecords']['kb_nescefe'] = 'ThinkopenAt\KbNescefe\Hooks\ImportExport->updateColPos';
/* ------------- HOOKS: Import/Export --------------- end ----------------- */




/* ------------- SLOTS: Page module --------------- begin --------------- */
if (TYPO3_MODE === 'BE') {
	/** @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher $signalSlotDispatcher */
	$signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\SignalSlot\\Dispatcher');
	$signalSlotDispatcher->connect(
		'ThinkopenAt\KbNescefe\AlternateImplementations\PageLayoutView',
		'getTable_tt_content:renderedColumn',
		'ThinkopenAt\KbNescefe\Slots\PageLayoutView',
		'handleRenderedColumn'
	);
}
/* ------------- SLOTS: Page module --------------- end ----------------- */



/* ------------- Alternate implementations --------------- begin --------------- */
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\CMS\Backend\View\PageLayoutView']['className'] = 'ThinkopenAt\KbNescefe\AlternateImplementations\PageLayoutView';

// These below are only needed until patch #@todo got integrated into extbase.
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\CMS\Extbase\Persistence\Generic\Storage\Typo3DbBackend']['className'] = 'ThinkopenAt\KbNescefe\AlternateImplementations\Typo3DbBackend';
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings']['className'] = 'ThinkopenAt\KbNescefe\AlternateImplementations\Typo3QuerySettings';
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface']['className'] = 'ThinkopenAt\KbNescefe\AlternateImplementations\Typo3QuerySettings';
/* ------------- Alternate implementations --------------- end ----------------- */



/* ------------- EXTDIRECT: Drag & Drop Handling --------------- begin --------------- */
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ExtDirect']['TYPO3.Components.DragAndDrop.CommandController']['originalCallbackClass'] = $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ExtDirect']['TYPO3.Components.DragAndDrop.CommandController']['callbackClass'];
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ExtDirect']['TYPO3.Components.DragAndDrop.CommandController']['callbackClass'] = 'ThinkopenAt\KbNescefe\Extdirect\ExtdirectPageCommands';
/* ------------- EXTDIRECT: Drag & Drop Handling --------------- end ----------------- */



/* ------------- Soft reference processing --------------- begin --------------- */
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['softRefParser']['kbnescefe_layout'] = 'ThinkopenAt\KbNescefe\Database\SoftReferenceProcessor';
/* ------------- Soft reference processing --------------- end ----------------- */


/* ------------- Frontend Plugin --------------- begin --------------- */
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'ThinkopenAt.KbNescefe',
	'Pi1',
	array(
		'Frontend' => 'render',
	)
);
/* ------------- Frontend Plugin --------------- end --------------- */




