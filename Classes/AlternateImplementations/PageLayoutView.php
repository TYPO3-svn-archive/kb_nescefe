<?php
namespace ThinkopenAt\KbNescefe\AlternateImplementations;

/***************************************************************
*  Copyright notice
*
*  (c) 2014-2015 Bernhard Kraft (kraftb@think-open.at)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * Extends "PageLayoutView"
 *
 * @author	Bernhard Kraft <kraftb@think-open.at>
 */

use \TYPO3\CMS\Backend\Utility\BackendUtility;
use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Backend\Utility\IconUtility;
use \TYPO3\CMS\Core\Versioning\VersionState;
use \TYPO3\CMS\Lang\LanguageService;

class PageLayoutView extends \TYPO3\CMS\Backend\View\PageLayoutView {

	/**
	 * @var integer The uid which should get set for an element in a container (configured via ext_conf_template)
	 */
	protected $containerElementColPos = 0;

	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManager
	 * @inject
	 */
	protected $objectManager;

	/**
	 * @var \ThinkopenAt\KbNescefe\Domain\Repository\ContentRepository The content element repository
	 * @inject
	 */
	protected $contentRepository = NULL;

	/**
	 * The constructor for a page layout view
	 *
	 * @return void
	 */
	public function __construct() {
		$this->containerElementColPos = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kb_nescefe']['containerElementColPos'];
		$this->backPath = $GLOBALS['BACK_PATH'];
		$this->kbNescefeContext = GeneralUtility::makeInstance('ThinkopenAt\KbNescefe\Context\Backend');
		$this->objectManager = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
		$this->contentRepository = $this->objectManager->get('ThinkopenAt\KbNescefe\Domain\Repository\ContentRepository');
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\CMS\Backend\Clipboard\Clipboard']['className'] = 'ThinkopenAt\KbNescefe\AlternateImplementations\Clipboard';
		if (method_exists(parent, '__construct')) {
			parent::__construct();
		}
	}

	/**
	 * Renders Content Elements from the tt_content table from page id
	 *
	 * This method is an exact copy from \TYPO3\CMS\Backend\View\PageLayoutView
	 * The only difference is enclosed in a comment marker "// --- CHANGED for kb_nescefe ---- begin ---------"
	 * and an according "end" marker. Indeed only a signal will get emitted after generating
	 * a content element head/content.
	 *
	 * @param integer $id Page id
	 * @return string HTML for the listing
	 * @todo Define visibility
	 */
	public function getTable_tt_content($id) {
		$this->initializeLanguages();
		$this->initializeClipboard();
		// Initialize:
		$RTE = $this->getBackendUser()->isRTE();
		$lMarg = 1;
		$showHidden = $this->tt_contentConfig['showHidden'] ? '' : BackendUtility::BEenableFields('tt_content');
		$pageTitleParamForAltDoc = '&recTitle=' . rawurlencode(BackendUtility::getRecordTitle('pages', BackendUtility::getRecordWSOL('pages', $id), TRUE));
		/** @var $pageRenderer \TYPO3\CMS\Core\Page\PageRenderer */
		$pageRenderer = $this->getPageLayoutController()->doc->getPageRenderer();
		$pageRenderer->loadExtJs();
		$pageRenderer->addJsFile($GLOBALS['BACK_PATH'] . 'sysext/cms/layout/js/typo3pageModule.js');
		// Get labels for CTypes and tt_content element fields in general:
		$this->CType_labels = array();
		foreach ($GLOBALS['TCA']['tt_content']['columns']['CType']['config']['items'] as $val) {
			$this->CType_labels[$val[1]] = $this->getLanguageService()->sL($val[0]);
		}
		$this->itemLabels = array();
		foreach ($GLOBALS['TCA']['tt_content']['columns'] as $name => $val) {
			$this->itemLabels[$name] = $this->getLanguageService()->sL($val['label']);
		}
		$languageColumn = array();
		$out = '';
		// Select display mode:
		// MULTIPLE column display mode, side by side:
		if (!$this->tt_contentConfig['single']) {
			// Setting language list:
			$langList = $this->tt_contentConfig['sys_language_uid'];
			if ($this->tt_contentConfig['languageMode']) {
				if ($this->tt_contentConfig['languageColsPointer']) {
					$langList = '0,' . $this->tt_contentConfig['languageColsPointer'];
				} else {
					$langList = implode(',', array_keys($this->tt_contentConfig['languageCols']));
				}
				$languageColumn = array();
			}
			$langListArr = GeneralUtility::intExplode(',', $langList);
			$defLanguageCount = array();
			$defLangBinding = array();
			// For each languages... :
			// If not languageMode, then we'll only be through this once.
			foreach ($langListArr as $lP) {
				$lP = (int)$lP;
				if (count($langListArr) === 1 || $lP === 0) {
					$showLanguage = ' AND sys_language_uid IN (' . $lP . ',-1)';
				} else {
					$showLanguage = ' AND sys_language_uid=' . $lP;
				}
				$cList = explode(',', $this->tt_contentConfig['cols']);
				$content = array();
				$head = array();

				// Select content records per column
				$contentRecordsPerColumn = $this->getContentRecordsPerColumn('table', $id, array_values($cList), $showHidden . $showLanguage);
				// For each column, render the content into a variable:
				foreach ($cList as $key) {
					if (!$lP) {
						$defLanguageCount[$key] = array();
					}
					// Start wrapping div
					$content[$key] .= '<div class="t3-page-ce-wrapper';
					if (count($contentRecordsPerColumn[$key]) === 0) {
						$content[$key] .= ' t3-page-ce-empty';
					}
					$content[$key] .= '">';
					// Add new content at the top most position
					$content[$key] .= '
					<div class="t3-page-ce" id="' . uniqid() . '">
						<div class="t3-page-ce-dropzone" id="colpos-' . $key . '-' . 'page-' . $id . '-' . uniqid() . '">
							<div class="t3-page-ce-wrapper-new-ce">
								<a href="#" onclick="' . htmlspecialchars($this->newContentElementOnClick($id, $key, $lP))
									. '" title="' . $this->getLanguageService()->getLL('newRecordHere', TRUE) . '">'
									. IconUtility::getSpriteIcon('actions-document-new') . '</a>
							</div>
						</div>
					</div>
					';
					$editUidList = '';
					$rowArr = $contentRecordsPerColumn[$key];
					$this->generateTtContentDataArray($rowArr);
					foreach ((array) $rowArr as $rKey => $row) {
						if ($this->tt_contentConfig['languageMode']) {
							$languageColumn[$key][$lP] = $head[$key] . $content[$key];
							if (!$this->defLangBinding) {
								$languageColumn[$key][$lP] .= $this->newLanguageButton(
									$this->getNonTranslatedTTcontentUids($defLanguageCount[$key], $id, $lP),
									$lP
								);
							}
						}
						if (is_array($row) && !VersionState::cast($row['t3ver_state'])->equals(VersionState::DELETE_PLACEHOLDER)) {
							$singleElementHTML = '';
							if (!$lP && ($this->defLangBinding || $row['sys_language_uid'] != -1)) {
								$defLanguageCount[$key][] = $row['uid'];
							}
							$editUidList .= $row['uid'] . ',';
							$disableMoveAndNewButtons = $this->defLangBinding && $lP > 0;
							if (!$this->tt_contentConfig['languageMode']) {
								$singleElementHTML .= '<div class="t3-page-ce-dragitem" id="' . uniqid() . '">';
							}
							$singleElementHTML .= $this->tt_content_drawHeader(
								$row,
								$this->tt_contentConfig['showInfo'] ? 15 : 5,
								$disableMoveAndNewButtons,
								TRUE,
								!$this->tt_contentConfig['languageMode']
							);
							$isRTE = $RTE && $this->isRTEforField('tt_content', $row, 'bodytext');
							$innerContent = '<div ' . ($row['_ORIG_uid'] ? ' class="ver-element"' : '') . '>'
								. $this->tt_content_drawItem($row, $isRTE) . '</div>';
							$singleElementHTML .= '<div class="t3-page-ce-body-inner">' . $innerContent . '</div>'
								. $this->tt_content_drawFooter($row);
							// NOTE: this is the end tag for <div class="t3-page-ce-body">
							// because of bad (historic) conception, starting tag has to be placed inside tt_content_drawHeader()
							$singleElementHTML .= '</div>';
							$statusHidden = $this->isDisabled('tt_content', $row) ? ' t3-page-ce-hidden' : '';
							$singleElementHTML = '<div class="t3-page-ce' . $statusHidden . '" id="element-tt_content-'
								. $row['uid'] . '">' . $singleElementHTML . '</div>';
							if ($this->tt_contentConfig['languageMode']) {
								$singleElementHTML .= '<div class="t3-page-ce">';
							}
							$singleElementHTML .= '<div class="t3-page-ce-dropzone" id="colpos-' . $key . '-' . 'page-' . $id .
								'-' . uniqid() . '">';
							// Add icon "new content element below"
							if (!$disableMoveAndNewButtons) {
								// New content element:
								if ($this->option_newWizard) {
									$onClick = 'window.location.href=\'db_new_content_el.php?id=' . $row['pid']
										. '&sys_language_uid=' . $row['sys_language_uid'] . '&colPos=' . $row['colPos']
										. '&uid_pid=' . -$row['uid'] .
										'&returnUrl=' . rawurlencode(GeneralUtility::getIndpEnv('REQUEST_URI')) . '\';';
								} else {
									$params = '&edit[tt_content][' . -$row['uid'] . ']=new';
									$onClick = BackendUtility::editOnClick($params, $this->backPath);
								}
								$singleElementHTML .= '
									<div class="t3-page-ce-wrapper-new-ce">
										<a href="#" onclick="' . htmlspecialchars($onClick) . '" title="'
											. $this->getLanguageService()->getLL('newRecordHere', TRUE) . '">'
											. IconUtility::getSpriteIcon('actions-document-new') . '</a>
									</div>
								';
							}
							$singleElementHTML .= '</div></div>';
							if ($this->defLangBinding && $this->tt_contentConfig['languageMode']) {
								$defLangBinding[$key][$lP][$row[$lP ? 'l18n_parent' : 'uid']] = $singleElementHTML;
							} else {
								$content[$key] .= $singleElementHTML;
							}
						} else {
							unset($rowArr[$rKey]);
						}
					}
					$content[$key] .= '</div>';
					// Add new-icon link, header:
					$newP = $this->newContentElementOnClick($id, $key, $lP);
					$colTitle = BackendUtility::getProcessedValue('tt_content', 'colPos', $key);
					$tcaItems = GeneralUtility::callUserFunction('TYPO3\\CMS\\Backend\\View\\BackendLayoutView->getColPosListItemsParsed', $id, $this);
					foreach ($tcaItems as $item) {
						if ($item[1] == $key) {
							$colTitle = $this->getLanguageService()->sL($item[0]);
						}
					}

					$pasteP = array('colPos' => $key, 'sys_language_uid' => $lP);
					$editParam = $this->doEdit && count($rowArr)
						? '&edit[tt_content][' . $editUidList . ']=edit' . $pageTitleParamForAltDoc
						: '';
					$head[$key] .= $this->tt_content_drawColHeader($colTitle, $editParam, $newP, $pasteP);
					// --- CHANGED for kb_nescefe ---- begin ---------"
					$this->getSignalSlotDispatcher()->dispatch(__CLASS__, 'getTable_tt_content:renderedColumn', array($head[$key], $content[$key]));
					// --- CHANGED for kb_nescefe ---- end ---------"
				}
				// For each column, fit the rendered content into a table cell:
				$out = '';
				if ($this->tt_contentConfig['languageMode']) {
					// in language mode process the content elements, but only fill $languageColumn. output will be generated later
					foreach ($cList as $key) {
						$languageColumn[$key][$lP] = $head[$key] . $content[$key];
						if (!$this->defLangBinding) {
							$languageColumn[$key][$lP] .= $this->newLanguageButton(
								$this->getNonTranslatedTTcontentUids($defLanguageCount[$key], $id, $lP),
								$lP
							);
						}
					}
				} else {
					$backendLayout = $this->getBackendLayoutView()->getSelectedBackendLayout($this->id);
					// GRID VIEW:
					$grid = '<div class="t3-gridContainer"><table border="0" cellspacing="0" cellpadding="0" width="100%" height="100%" class="t3-page-columns t3-gridTable">';
					// Add colgroups
					$colCount = (int)$backendLayout['__config']['backend_layout.']['colCount'];
					$rowCount = (int)$backendLayout['__config']['backend_layout.']['rowCount'];
					$grid .= '<colgroup>';
					for ($i = 0; $i < $colCount; $i++) {
						$grid .= '<col style="width:' . 100 / $colCount . '%"></col>';
					}
					$grid .= '</colgroup>';
					// Cycle through rows
					for ($row = 1; $row <= $rowCount; $row++) {
						$rowConfig = $backendLayout['__config']['backend_layout.']['rows.'][$row . '.'];
						if (!isset($rowConfig)) {
							continue;
						}
						$grid .= '<tr>';
						for ($col = 1; $col <= $colCount; $col++) {
							$columnConfig = $rowConfig['columns.'][$col . '.'];
							if (!isset($columnConfig)) {
								continue;
							}
							// Which tt_content colPos should be displayed inside this cell
							$columnKey = (int)$columnConfig['colPos'];
							// Render the grid cell
							$colSpan = (int)$columnConfig['colspan'];
							$rowSpan = (int)$columnConfig['rowspan'];
							$grid .= '<td valign="top"' .
								($colSpan > 0 ? ' colspan="' . $colSpan . '"' : '') .
								($rowSpan > 0 ? ' rowspan="' . $rowSpan . '"' : '') .
								' class="t3-gridCell t3-page-column t3-page-column-' . $columnKey .
								((!isset($columnConfig['colPos']) || $columnConfig['colPos'] === '') ? ' t3-gridCell-unassigned' : '') .
								((isset($columnConfig['colPos']) && $columnConfig['colPos'] !== '' && !$head[$columnKey]) ? ' t3-gridCell-restricted' : '') .
								($colSpan > 0 ? ' t3-gridCell-width' . $colSpan : '') .
								($rowSpan > 0 ? ' t3-gridCell-height' . $rowSpan : '') . '">';

							// Draw the pre-generated header with edit and new buttons if a colPos is assigned.
							// If not, a new header without any buttons will be generated.
							if (isset($columnConfig['colPos']) && $columnConfig['colPos'] !== '' && $head[$columnKey]) {
								$grid .= $head[$columnKey] . $content[$columnKey];
							} elseif (isset($columnConfig['colPos']) && $columnConfig['colPos'] !== '') {
								$grid .= $this->tt_content_drawColHeader($this->getLanguageService()->getLL('noAccess'), '', '');
							} elseif (isset($columnConfig['name']) && strlen($columnConfig['name']) > 0) {
								$grid .= $this->tt_content_drawColHeader($this->getLanguageService()->sL($columnConfig['name'])
									. ' (' . $this->getLanguageService()->getLL('notAssigned') . ')', '', '');
							} else {
								$grid .= $this->tt_content_drawColHeader($this->getLanguageService()->getLL('notAssigned'), '', '');
							}

							$grid .= '</td>';
						}
						$grid .= '</tr>';
					}
					$out .= $grid . '</table></div>';
				}
				// CSH:
				$out .= BackendUtility::cshItem($this->descrTable, 'columns_multi', $GLOBALS['BACK_PATH']);
			}
			// If language mode, then make another presentation:
			// Notice that THIS presentation will override the value of $out!
			// But it needs the code above to execute since $languageColumn is filled with content we need!
			if ($this->tt_contentConfig['languageMode']) {
				// Get language selector:
				$languageSelector = $this->languageSelector($id);
				// Reset out - we will make new content here:
				$out = '';
				// Traverse languages found on the page and build up the table displaying them side by side:
				$cCont = array();
				$sCont = array();
				foreach ($langListArr as $lP) {
					// Header:
					$lP = (int)$lP;
					$cCont[$lP] = '
						<td valign="top" class="t3-page-lang-column">
							<h3>' . htmlspecialchars($this->tt_contentConfig['languageCols'][$lP]) . '</h3>
						</td>';

					// "View page" icon is added:
					$onClick = BackendUtility::viewOnClick($this->id, $this->backPath, BackendUtility::BEgetRootLine($this->id), '', '', ('&L=' . $lP));
					$viewLink = '<a href="#" onclick="' . htmlspecialchars($onClick) . '">' . IconUtility::getSpriteIcon('actions-document-view') . '</a>';
					// Language overlay page header:
					if ($lP) {
						list($lpRecord) = BackendUtility::getRecordsByField('pages_language_overlay', 'pid', $id, 'AND sys_language_uid=' . $lP);
						BackendUtility::workspaceOL('pages_language_overlay', $lpRecord);
						$params = '&edit[pages_language_overlay][' . $lpRecord['uid'] . ']=edit&overrideVals[pages_language_overlay][sys_language_uid]=' . $lP;
						$lPLabel = $this->getPageLayoutController()->doc->wrapClickMenuOnIcon(
							IconUtility::getSpriteIconForRecord('pages_language_overlay', $lpRecord),
							'pages_language_overlay',
							$lpRecord['uid']
						) . $viewLink . ($this->getBackendUser()->check('tables_modify', 'pages_language_overlay')
								? '<a href="#" onclick="' . htmlspecialchars(BackendUtility::editOnClick($params, $this->backPath))
									. '" title="' . $this->getLanguageService()->getLL('edit', TRUE) . '">'
									. IconUtility::getSpriteIcon('actions-document-open') . '</a>'
								: ''
							) . htmlspecialchars(GeneralUtility::fixed_lgd_cs($lpRecord['title'], 20));
					} else {
						$lPLabel = $viewLink;
					}
					$sCont[$lP] = '
						<td nowrap="nowrap" class="t3-page-lang-column t3-page-lang-label">' . $lPLabel . '</td>';
				}
				// Add headers:
				$out .= '<tr>' . implode($cCont) . '</tr>';
				$out .= '<tr>' . implode($sCont) . '</tr>';
				// Traverse previously built content for the columns:
				foreach ($languageColumn as $cKey => $cCont) {
					$out .= '
					<tr>
						<td valign="top" class="t3-gridCell t3-page-column t3-page-lang-column">' . implode(('</td>' . '
						<td valign="top" class="t3-gridCell t3-page-column t3-page-lang-column">'), $cCont) . '</td>
					</tr>';
					if ($this->defLangBinding) {
						// "defLangBinding" mode
						foreach ($defLanguageCount[$cKey] as $defUid) {
							$cCont = array();
							foreach ($langListArr as $lP) {
								$cCont[] = $defLangBinding[$cKey][$lP][$defUid] . $this->newLanguageButton(
									$this->getNonTranslatedTTcontentUids(array($defUid), $id, $lP),
									$lP
								);
							}
							$out .= '
							<tr>
								<td valign="top" class="t3-page-lang-column">' . implode(('</td>' . '
								<td valign="top" class="t3-page-lang-column">'), $cCont) . '</td>
							</tr>';
						}
						// Create spacer:
						$cCont = array_fill(0, count($langListArr), '&nbsp;');
						$out .= '
						<tr>
							<td valign="top" class="t3-page-lang-column">' . implode(('</td>' . '
							<td valign="top" class="t3-page-lang-column">'), $cCont) . '</td>
						</tr>';
					}
				}
				// Finally, wrap it all in a table and add the language selector on top of it:
				$out = $languageSelector . '
					<div class="t3-lang-gridContainer">
						<table cellpadding="0" cellspacing="0" class="t3-page-langMode">
							' . $out . '
						</table>
					</div>';
				// CSH:
				$out .= BackendUtility::cshItem($this->descrTable, 'language_list', $GLOBALS['BACK_PATH']);
			}
		} else {
			// SINGLE column mode (columns shown beneath each other):
			if ($this->tt_contentConfig['sys_language_uid'] == 0 || !$this->defLangBinding) {
				// Initialize:
				if ($this->defLangBinding && $this->tt_contentConfig['sys_language_uid'] == 0) {
					$showLanguage = ' AND sys_language_uid IN (0,-1)';
					$lP = 0;
				} else {
					$showLanguage = ' AND sys_language_uid=' . $this->tt_contentConfig['sys_language_uid'];
					$lP = $this->tt_contentConfig['sys_language_uid'];
				}
				$cList = explode(',', $this->tt_contentConfig['showSingleCol']);
				$out = '';
				// Expand the table to some preset dimensions:
				$out .= '
					<tr>
						<td><img src="clear.gif" width="' . $lMarg . '" height="1" alt="" /></td>
						<td valign="top"><img src="clear.gif" width="150" height="1" alt="" /></td>
						<td><img src="clear.gif" width="10" height="1" alt="" /></td>
						<td valign="top"><img src="clear.gif" width="300" height="1" alt="" /></td>
					</tr>';

				// Select content records per column
				$contentRecordsPerColumn = $this->getContentRecordsPerColumn('tt_content', $id, array_values($cList), $showHidden . $showLanguage);
				// Traverse columns to display top-on-top
				foreach ($cList as $counter => $key) {
					$c = 0;
					$rowArr = $contentRecordsPerColumn[$key];
					$this->generateTtContentDataArray($rowArr);
					$numberOfContentElementsInColumn = count($rowArr);
					$rowOut = '';
					// If it turns out that there are not content elements in the column, then display a big button which links directly to the wizard script:
					if ($this->doEdit && $this->option_showBigButtons && !(int)$key && $numberOfContentElementsInColumn == 0) {
						$onClick = 'window.location.href=\'db_new_content_el.php?id=' . $id . '&colPos=' . (int)$key
							. '&sys_language_uid=' . $lP . '&uid_pid=' . $id . '&returnUrl='
							. rawurlencode(GeneralUtility::getIndpEnv('REQUEST_URI')) . '\';';
						$theNewButton = $this->getPageLayoutController()->doc->t3Button($onClick, $this->getLanguageService()->getLL('newPageContent'));
						$theNewButton = '<img src="clear.gif" width="1" height="5" alt="" /><br />' . $theNewButton;
					} else {
						$theNewButton = '';
					}
					$editUidList = '';
					// Traverse any selected elements:
					foreach ($rowArr as $rKey => $row) {
						if (is_array($row) && !VersionState::cast($row['t3ver_state'])->equals(VersionState::DELETE_PLACEHOLDER)) {
							$c++;
							$editUidList .= $row['uid'] . ',';
							$isRTE = $RTE && $this->isRTEforField('tt_content', $row, 'bodytext');
							// Create row output:
							$rowOut .= '
								<tr>
									<td></td>
									<td valign="top">' . $this->tt_content_drawHeader($row) . '</td>
									<td>&nbsp;</td>
									<td' . ($row['_ORIG_uid'] ? ' class="ver-element"' : '') . ' valign="top">'
										. $this->tt_content_drawItem($row, $isRTE) . '</td>
								</tr>';
							// If the element was not the last element, add a divider line:
							if ($c != $numberOfContentElementsInColumn) {
								$rowOut .= '
								<tr>
									<td></td>
									<td colspan="3"><img'
									. IconUtility::skinImg($this->backPath, 'gfx/stiblet_medium2.gif', 'width="468" height="1"')
									. ' class="c-divider" alt="" /></td>
								</tr>';
							}
						} else {
							unset($rowArr[$rKey]);
						}
					}
					// Add spacer between sections in the vertical list
					if ($counter) {
						$out .= '
							<tr>
								<td></td>
								<td colspan="3"><br /><br /><br /><br /></td>
							</tr>';
					}
					// Add section header:
					$newP = $this->newContentElementOnClick($id, $key, $this->tt_contentConfig['sys_language_uid']);
					$pasteP = array('colPos' => $key, 'sys_language_uid' => $this->tt_contentConfig['sys_language_uid']);
					$out .= '

						<!-- Column header: -->
						<tr>
							<td></td>
							<td valign="top" colspan="3">' . $this->tt_content_drawColHeader(
								BackendUtility::getProcessedValue('tt_content', 'colPos', $key),
								$this->doEdit && count($rowArr) ? '&edit[tt_content][' . $editUidList . ']=edit' . $pageTitleParamForAltDoc : '',
								$newP,
								$pasteP
							) . $theNewButton . '<br /></td>
						</tr>';
					// Finally, add the content from the records in this column:
					$out .= $rowOut;
				}
				// Finally, wrap all table rows in one, big table:
				$out = '
					<table border="0" cellpadding="0" cellspacing="0" width="400" class="typo3-page-columnsMode">
						' . $out . '
					</table>';
				// CSH:
				$out .= BackendUtility::cshItem($this->descrTable, 'columns_single', $GLOBALS['BACK_PATH']);
			} else {
				$out = '<br/><br/>' . $this->getPageLayoutController()->doc->icons(1)
					. 'Sorry, you cannot view a single language in this localization mode (Default Language Binding is enabled)<br/><br/>';
			}
		}
		// Add the big buttons to page:
		if ($this->option_showBigButtons) {
			$bArray = array();
			if (!$this->getPageLayoutController()->current_sys_language) {
				if ($this->ext_CALC_PERMS & 2) {
					$bArray[0] = $this->getPageLayoutController()->doc->t3Button(
						BackendUtility::editOnClick('&edit[pages][' . $id . ']=edit', $this->backPath, ''),
						$this->getLanguageService()->getLL('editPageProperties')
					);
				}
			} else {
				if ($this->doEdit && $this->getBackendUser()->check('tables_modify', 'pages_language_overlay')) {
					list($languageOverlayRecord) = BackendUtility::getRecordsByField(
						'pages_language_overlay',
						'pid',
						$id,
						'AND sys_language_uid=' . (int)$this->getPageLayoutController()->current_sys_language
					);
					$bArray[0] = $this->getPageLayoutController()->doc->t3Button(
						BackendUtility::editOnClick('&edit[pages_language_overlay][' . $languageOverlayRecord['uid'] . ']=edit',
							$this->backPath, ''),
						$this->getLanguageService()->getLL('editPageProperties_curLang')
					);
				}
			}
			if ($this->ext_CALC_PERMS & 4 || $this->ext_CALC_PERMS & 2) {
				$bArray[1] = $this->getPageLayoutController()->doc->t3Button(
					'window.location.href=\'' . $this->backPath . 'move_el.php?table=pages&uid=' . $id
						. '&returnUrl=' . rawurlencode(GeneralUtility::getIndpEnv('REQUEST_URI')) . '\';',
					$this->getLanguageService()->getLL('move_page')
				);
			}
			if ($this->ext_CALC_PERMS & 8) {
				$bArray[2] = $this->getPageLayoutController()->doc->t3Button(
					'window.location.href=\'' . $this->backPath . 'db_new.php?id=' . $id
						. '&pagesOnly=1&returnUrl=' . rawurlencode(GeneralUtility::getIndpEnv('REQUEST_URI')) . '\';',
					$this->getLanguageService()->getLL('newPage2')
				);
			}
			if ($this->doEdit && $this->ext_function == 1) {
				$bArray[3] = $this->getPageLayoutController()->doc->t3Button(
					'window.location.href=\'db_new_content_el.php?id=' . $id
						. '&sys_language_uid=' . $this->getPageLayoutController()->current_sys_language
						. '&returnUrl=' . rawurlencode(GeneralUtility::getIndpEnv('REQUEST_URI')) . '\';',
					$this->getLanguageService()->getLL('newPageContent2')
				);
			}
			$out = '
				<table border="0" cellpadding="4" cellspacing="0" class="typo3-page-buttons">
					<tr>
						<td>' . implode('</td>
						<td>', $bArray) . '</td>
						<td>' . BackendUtility::cshItem($this->descrTable, 'button_panel', $GLOBALS['BACK_PATH']) . '</td>
					</tr>
				</table>
				<br />
				' . $out;
		}
		// Return content:
		return $out;
	}

	/**
	 * Get the SignalSlot dispatcher
	 *
	 * @return \TYPO3\CMS\Extbase\SignalSlot\Dispatcher
	 */
	protected function getSignalSlotDispatcher() {
		return GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\SignalSlot\\Dispatcher');
	}

	/**
	 * Creates onclick-attribute content for a new content element
	 * Modified copy from: typo3/sysext/cms/layout/class.tx_cms_layout.php
	 *
	 * @param integer Page id where to create the element.
	 * @param integer $colPos Preset: Column position value
	 * @param integer Preset: Sys language value
	 * @return string String for onclick attribute.
	 * @see getTable_tt_content()
	 */
	public function newContentElementOnClick($id, $colPos, $sys_language) {
		$element = $this->kbNescefeContext->getRenderedElement();
		if (! $element instanceof \ThinkopenAt\KbNescefe\Domain\Model\Content) {
			return parent::newContentElementOnClick($id, $colPos, $sys_language);
		}
		if ($this->kbNescefeContext->getOptionNewWizard()) {
			$onClick = 'window.location.href=\'db_new_content_el.php?';
			$onClick .= 'id=' . $id;
			$onClick .= '&colPos=' . $this->containerElementColPos;
			$onClick .= '&sys_language_uid=' . $element->getSysLanguageUid();
			$onClick .= '&kbnescefe_parentPosition=' . $this->kbNescefeContext->getElementPosition();
			$onClick .= '&kbnescefe_parentElement=' . $element->getUid();
			$onClick .= '&uid_pid=' . $id;
			$onClick .= '&returnUrl=' . rawurlencode(GeneralUtility::getIndpEnv('REQUEST_URI')) . '\';';
		} else {
			$editParam = '&edit[tt_content][' . $id. ']=new';
			$editParam .= '&defVals[tt_content][colPos]=' . $this->containerElementColPos;
			$editParam .= '&defVals[tt_content][sys_language_uid]=' . $element->getSysLanguageUid();
			$editParam .= '&defVals[tt_content][kbnescefe_parentPosition]=' . $this->kbNescefeContext->getElementPosition();
			$editParam .= '&defVals[tt_content][kbnescefe_parentElement]=' . $element->getUid();
			$onClick = BackendUtility::editOnClick($editParam, $this->backPath);
		}
		return $onClick;
	}


	/**
	 * Only passes "pasteParams" along if none of the elements on the clipboard is a parent container
	 * of the currently rendered column. Else this would result in an container getting pasted into
	 * itself. Of course this is possible for copy-mode.
	 *
	 * @param string $colName Column name
	 * @param string $editParams Edit params (Syntax: &edit[...] for alt_doc.php)
	 * @param string $newParams New element params (Syntax: &edit[...] for alt_doc.php) OBSOLETE
	 * @param array|NULL $pasteParams Paste element params (i.e. array(colPos => 1, sys_language_uid => 2))
	 * @return string HTML table
	 */
	public function tt_content_drawColHeader($colName, $editParams, $newParams, array $pasteParams = NULL) {
		if ( $pasteParams && $this->clipboard->currentMode() === 'cut' ) {
			$element = $this->kbNescefeContext->getRenderedElement();
			if (
				$element instanceof \ThinkopenAt\KbNescefe\Domain\Model\Content &&
				$this->contentRepository->clipboardContainsParent($this->clipboard, $element)
			) {
				$pasteParams = NULL;
			}
		}
		return parent::tt_content_drawColHeader($colName, $editParams, $newParams, $pasteParams);
	}



}

