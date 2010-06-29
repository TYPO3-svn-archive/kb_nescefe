<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2006-2010 Bernhard Kraft (kraftb@think-open.at)
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
 * Extends the page template
 *
 * @author	Bernhard Kraft <kraftb@think-open.at>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 */


require_once(PATH_t3lib.'class.t3lib_clipboard.php');
require_once(t3lib_extMgm::extPath('kb_nescefe').'class.tx_kbnescefe_func.php');

class ux_tx_cms_layout extends tx_cms_layout {


	/**
	 * Draw the header for a single tt_content element
	 *
	 * @param	array		Record array
	 * @param	integer		Amount of pixel space above the header.
	 * @param	boolean		If set the buttons for creating new elements and moving up and down are not shown.
	 * @param	boolean		If set, we are in language mode and flags will be shown for languages
	 * @return	string		HTML table with the record header.
	 */
	function tt_content_drawHeader($row,$space=0,$disableMoveAndNewButtons=FALSE,$langMode=FALSE)	{
		global $TCA;
		if (t3lib_div::compat_version('4.4')) {
			return $this->tt_content_drawHeader_44($row, $space, $disableMoveAndNewButtons, $langMode);
		}

			// Load full table description:
		t3lib_div::loadTCA('tt_content');

			// Get record locking status:
		if ($lockInfo=t3lib_BEfunc::isRecordLocked('tt_content',$row['uid']))	{
			$lockIcon='<a href="#" onclick="'.htmlspecialchars('alert('.$GLOBALS['LANG']->JScharCode($lockInfo['msg']).');return false;').'">'.
						'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/recordlock_warning3.gif','width="17" height="12"').' title="'.htmlspecialchars($lockInfo['msg']).'" alt="" />'.
						'</a>';
		} else $lockIcon='';

			// Call stats information hook
		$stat = '';
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['recStatInfoHooks']))	{
			$_params = array('tt_content',$row['uid']);
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['recStatInfoHooks'] as $_funcRef)	{
				$stat.=t3lib_div::callUserFunction($_funcRef,$_params,$this);
			}
		}

			// Create header with icon/lock-icon/title:
		$header = $this->getIcon('tt_content',$row).
				$lockIcon.
				$stat.
				($langMode ? $this->languageFlag($row['sys_language_uid']) : '').
				'&nbsp;<b>'.htmlspecialchars($this->CType_labels[$row['CType']]).'</b>';
		$out = array();
		$out['header'] = '
					<tr>
						<td class="bgColor4">'.$header.'</td>
					</tr>';

			// If show info is set...;
		if ($this->tt_contentConfig['showInfo'])	{

				// Get processed values:
			$info = Array();
			$this->getProcessedValue('tt_content','hidden,starttime,endtime,fe_group,spaceBefore,spaceAfter,section_frame,sectionIndex,linkToTop',$row,$info);

				// Render control panel for the element:
			if ($this->tt_contentConfig['showCommands'] && $this->doEdit)	{

					// Start control cell:
				$out['control_top'] = '
					<!-- Control Panel -->
					<tr>
						<td class="bgColor5">';

					// Edit content element:
				$params='&edit[tt_content]['.$this->tt_contentData['nextThree'][$row['uid']].']=edit';
				$out['edit'] ='<a href="#" onclick="'.htmlspecialchars(t3lib_BEfunc::editOnClick($params,$this->backPath)).'">'.
						'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/edit2.gif','width="11" height="12"').' title="'.htmlspecialchars($this->nextThree>1?sprintf($GLOBALS['LANG']->getLL('nextThree'),$this->nextThree):$GLOBALS['LANG']->getLL('edit')).'" alt="" />'.
						'</a>';

				if (!$disableMoveAndNewButtons)	{
						// New content element:
					if ($this->option_newWizard)	{
						$onClick="window.location.href='db_new_content_el.php?id=".$row['pid'].'&sys_language_uid='.$row['sys_language_uid'].'&colPos='.$row['colPos'].'&uid_pid='.(-$row['uid']).'&returnUrl='.rawurlencode(t3lib_div::getIndpEnv('REQUEST_URI'))."';";
					} else {
						$params='&edit[tt_content]['.(-$row['uid']).']=new';
						$onClick = t3lib_BEfunc::editOnClick($params,$this->backPath);
					}
					$out['new'] ='<a href="#" onclick="'.htmlspecialchars($onClick).'">'.
							'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/new_record.gif','width="16" height="12"').' title="'.$GLOBALS['LANG']->getLL('newAfter',1).'" alt="" />'.
							'</a>';

						// Move element up:
					if ($this->tt_contentData['prev'][$row['uid']])	{
						$params='&cmd[tt_content]['.$row['uid'].'][move]='.$this->tt_contentData['prev'][$row['uid']];
						$out['move_up'] ='<a href="'.htmlspecialchars($GLOBALS['SOBE']->doc->issueCommand($params)).'">'.
								'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/button_up.gif','width="11" height="10"').' title="'.$GLOBALS['LANG']->getLL('moveUp',1).'" alt="" />'.
								'</a>';
					} else {
						$out['move_up'] ='<img src="clear.gif" '.t3lib_iconWorks::skinImg($this->backPath,'gfx/button_up.gif','width="11" height="10"',2).' alt="" />';
					}
						// Move element down:
					if ($this->tt_contentData['next'][$row['uid']])	{
						$params='&cmd[tt_content]['.$row['uid'].'][move]='.$this->tt_contentData['next'][$row['uid']];
						$out['move_down'] ='<a href="'.htmlspecialchars($GLOBALS['SOBE']->doc->issueCommand($params)).'">'.
								'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/button_down.gif','width="11" height="10"').' title="'.$GLOBALS['LANG']->getLL('moveDown',1).'" alt="" />'.
								'</a>';
					} else {
						$out['move_down'] ='<img src="clear.gif" '.t3lib_iconWorks::skinImg($this->backPath,'gfx/button_down.gif','width="11" height="10"',2).' alt="" />';
					}
				}

					// Hide element:
				$hiddenField = $TCA['tt_content']['ctrl']['enablecolumns']['disabled'];
				if ($hiddenField && $TCA['tt_content']['columns'][$hiddenField] && (!$TCA['tt_content']['columns'][$hiddenField]['exclude'] || $GLOBALS['BE_USER']->check('non_exclude_fields','tt_content:'.$hiddenField)))	{
					if ($row[$hiddenField])	{
						$params='&data[tt_content]['.($row['_ORIG_uid'] ? $row['_ORIG_uid'] : $row['uid']).']['.$hiddenField.']=0';
						$out['hide'] ='<a href="'.htmlspecialchars($GLOBALS['SOBE']->doc->issueCommand($params)).'">'.
								'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/button_unhide.gif','width="11" height="10"').' title="'.$GLOBALS['LANG']->getLL('unHide',1).'" alt="" />'.
								'</a>';
					} else {
						$params='&data[tt_content]['.($row['_ORIG_uid'] ? $row['_ORIG_uid'] : $row['uid']).']['.$hiddenField.']=1';
						$out['hide'] ='<a href="'.htmlspecialchars($GLOBALS['SOBE']->doc->issueCommand($params)).'">'.
								'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/button_hide.gif','width="11" height="10"').' title="'.$GLOBALS['LANG']->getLL('hide',1).'" alt="" />'.
								'</a>';
					}
				}

					// Delete
				$params='&cmd[tt_content]['.$row['uid'].'][delete]=1';
				$out['delete'] ='<a href="'.htmlspecialchars($GLOBALS['SOBE']->doc->issueCommand($params)).'" onclick="'.htmlspecialchars('return confirm('.$GLOBALS['LANG']->JScharCode($GLOBALS['LANG']->getLL('deleteWarning')).');').'">'.
						'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/garbage.gif','width="11" height="12"').' title="'.$GLOBALS['LANG']->getLL('deleteItem',1).'" alt="" />'.
						'</a>';

					// End cell:
				$out['control_bottom'] = '
						</td>
					</tr>';
			}

				// Display info from records fields:
			if (count($info))	{
				$out['info'] = '
					<tr>
						<td class="bgColor4-20">'.implode('<br />',$info).'</td>
					</tr>';
			}
		}

		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['tt_content_drawHeader'])) {
			$_params = array('tt_content',$row['uid']);
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['tt_content_drawHeader'] as $_funcRef) {
				$_params = array(
					'content' => &$out,
					'space' => &$space,
					'row' => $row,
					'disableMoveAndNewButtons' => $disableMoveAndNewButtons,
					'langMode' => $langMode,
				);
				t3lib_div::callUserFunction($_funcRef,$_params,$this);
			}
		}
			// Wrap the whole header in a table:
		return '
				<table border="0" cellpadding="0" cellspacing="0" class="typo3-page-ceHeader">'.($space?'
					<tr>
						<td><img src="clear.gif" height="'.$space.'" alt="" /></td>
					</tr>':'').
					implode('', $out).'
				</table>';
	}


	/**
	 * Draw the header for a single tt_content element
	 *
	 * @param	array		Record array
	 * @param	integer		Amount of pixel space above the header.
	 * @param	boolean		If set the buttons for creating new elements and moving up and down are not shown.
	 * @param	boolean		If set, we are in language mode and flags will be shown for languages
	 * @return	string		HTML table with the record header.
	 */
	function tt_content_drawHeader_44($row,$space=0,$disableMoveAndNewButtons=FALSE,$langMode=FALSE) {
		global $TCA;

			// Load full table description:
		t3lib_div::loadTCA('tt_content');

			// Get record locking status:
		if ($lockInfo=t3lib_BEfunc::isRecordLocked('tt_content',$row['uid']))	{
			$lockIcon='<a href="#" onclick="'.htmlspecialchars('alert('.$GLOBALS['LANG']->JScharCode($lockInfo['msg']).');return false;').'" title="'.htmlspecialchars($lockInfo['msg']).'">'.
						t3lib_iconWorks::getSpriteIcon('status-warning-in-use') .
					'</a>';
		} else $lockIcon='';

			// Call stats information hook
		$stat = '';
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['recStatInfoHooks']))	{
			$_params = array('tt_content', $row['uid'], &$row);
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['recStatInfoHooks'] as $_funcRef)	{
				$stat.=t3lib_div::callUserFunction($_funcRef,$_params,$this);
			}
		}

			// Create line with type of content element and icon/lock-icon/title:
		$ceType = $this->getIcon('tt_content',$row) . ' ' .
				$lockIcon . ' ' .
				$stat . ' ' .
				($langMode ? $this->languageFlag($row['sys_language_uid']) : '') .  ' ' .
				'&nbsp;<strong>' . htmlspecialchars($this->CType_labels[$row['CType']]) . '</strong>';

		$out = array();
		$out['control_top'] = '<h4 class="t3-page-ce-header" style="white-space: nowrap !important;"><div class="t3-row-header">';

			// If show info is set...;
		if ($this->tt_contentConfig['showInfo'])	{

				// Get processed values:
			$info = Array();
			$this->getProcessedValue('tt_content', 'hidden,starttime,endtime,fe_group,spaceBefore,spaceAfter', $row, $info);

				// Render control panel for the element:
			if ($this->tt_contentConfig['showCommands'] && $this->doEdit)	{

				if (!$disableMoveAndNewButtons)	{
						// New content element:
					if ($this->option_newWizard)	{
						$onClick="window.location.href='db_new_content_el.php?id=".$row['pid'].'&sys_language_uid='.$row['sys_language_uid'].'&colPos='.$row['colPos'].'&uid_pid='.(-$row['uid']).'&returnUrl='.rawurlencode(t3lib_div::getIndpEnv('REQUEST_URI'))."';";
					} else {
						$params='&edit[tt_content]['.(-$row['uid']).']=new';
						$onClick = t3lib_BEfunc::editOnClick($params,$this->backPath);
					}
					$out['new'] = '<a href="#" onclick="' . htmlspecialchars($onClick) . '" title="' . $GLOBALS['LANG']->getLL('newAfter', 1) . '">' .
						t3lib_iconWorks::getSpriteIcon('actions-document-new') .
						'</a>';
				}

				// Edit content element:
				$params = '&edit[tt_content][' . $this->tt_contentData['nextThree'][$row['uid']] . ']=edit';
				$out['edit'] = '<a href="#" onclick="' . htmlspecialchars(t3lib_BEfunc::editOnClick($params, $this->backPath)) . '" title="' .
					htmlspecialchars($this->nextThree > 1 ? sprintf($GLOBALS['LANG']->getLL('nextThree'), $this->nextThree) : $GLOBALS['LANG']->getLL('edit')) . '">' .
					t3lib_iconWorks::getSpriteIcon('actions-document-open') .
					'</a>';

					// Hide element:
				$hiddenField = $TCA['tt_content']['ctrl']['enablecolumns']['disabled'];
				if ($hiddenField && $TCA['tt_content']['columns'][$hiddenField] && (!$TCA['tt_content']['columns'][$hiddenField]['exclude'] || $GLOBALS['BE_USER']->check('non_exclude_fields','tt_content:'.$hiddenField)))	{
					if ($row[$hiddenField])	{
						$params='&data[tt_content]['.($row['_ORIG_uid'] ? $row['_ORIG_uid'] : $row['uid']).']['.$hiddenField.']=0';
						$out['hide'] = '<a href="'.htmlspecialchars($GLOBALS['SOBE']->doc->issueCommand($params)).'" title="'.$GLOBALS['LANG']->getLL('unHide', TRUE).'">'.
									t3lib_iconWorks::getSpriteIcon('actions-edit-unhide') .
								'</a>';
					} else {
						$params='&data[tt_content]['.($row['_ORIG_uid'] ? $row['_ORIG_uid'] : $row['uid']).']['.$hiddenField.']=1';
						$out['hide'] = '<a href="'.htmlspecialchars($GLOBALS['SOBE']->doc->issueCommand($params)).'" title="'.$GLOBALS['LANG']->getLL('hide', TRUE).'">'.
									t3lib_iconWorks::getSpriteIcon('actions-edit-hide') .
								'</a>';
					}
				}

					// Delete
				$params='&cmd[tt_content]['.$row['uid'].'][delete]=1';
				$confirm = $GLOBALS['LANG']->JScharCode($GLOBALS['LANG']->getLL('deleteWarning') .
					t3lib_BEfunc::translationCount('tt_content', $row['uid'], ' ' . $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.xml:labels.translationsOfRecord')));
				$out['delete'] ='<a href="'.htmlspecialchars($GLOBALS['SOBE']->doc->issueCommand($params)).'" onclick="'.htmlspecialchars('return confirm('. $confirm .');').'" title="'.$GLOBALS['LANG']->getLL('deleteItem', TRUE).'">'.
							t3lib_iconWorks::getSpriteIcon('actions-edit-delete') . 
						'</a>';

				if (!$disableMoveAndNewButtons) {
					$out['move_wrap_begin'] = '<span class="t3-page-ce-icons-move">';
					// Move element up:
					if ($this->tt_contentData['prev'][$row['uid']]) {
						$params = '&cmd[tt_content][' . $row['uid'] . '][move]=' . $this->tt_contentData['prev'][$row['uid']];
						$out['move_up'] = '<a href="' . htmlspecialchars($GLOBALS['SOBE']->doc->issueCommand($params)) . '" title="' . $GLOBALS['LANG']->getLL('moveUp', TRUE) . '">' .
							t3lib_iconWorks::getSpriteIcon('actions-move-up') .
							'</a>';
					} else {
						$out['move_up'] = t3lib_iconWorks::getSpriteIcon('empty-empty');
					}
						// Move element down:
					if ($this->tt_contentData['next'][$row['uid']])	{
						$params = '&cmd[tt_content][' . $row['uid'] . '][move]= ' . $this->tt_contentData['next'][$row['uid']];
						$out['move_down'] = '<a href="' . htmlspecialchars($GLOBALS['SOBE']->doc->issueCommand($params)) . '" title="' . $GLOBALS['LANG']->getLL('moveDown', TRUE) . '">' .
							t3lib_iconWorks::getSpriteIcon('actions-move-down') .
							'</a>';
					} else {
						$out['move_down'] = t3lib_iconWorks::getSpriteIcon('empty-empty');
					}
					$out['move_wrap_end'] =  '</span>';
				}
			}

				// Display info from records fields:
			$infoOutput = '';
			if (count($info))	{
				$infoOutput = '<div class="t3-page-ce-info">
					' . implode('<br />', $info) . '
					</div>';
			}
		}
		$out['control_bottom'] = '</div></h4>';

			// NOTE: end-tag for <div class="t3-page-ce-body"> is in getTable_tt_content()
		$out['header'] = '<div class="t3-page-ce-body">
					<div class="t3-page-ce-type">
						' . $ceType . '
					</div>';

		$out['info'] = $infoOutput;

		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['tt_content_drawHeader'])) {
			$_params = array('tt_content',$row['uid']);
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['tt_content_drawHeader'] as $_funcRef) {
				$_params = array(
					'content' => &$out,
					'space' => &$space,
					'row' => $row,
					'disableMoveAndNewButtons' => $disableMoveAndNewButtons,
					'langMode' => $langMode,
				);
				t3lib_div::callUserFunction($_funcRef,$_params,$this);
			}
		}

			// Wrap the whole header
		return implode('', $out);
	}


	/**
	 * Draw header for a content element column:
	 *
	 * @param	string		Column name
	 * @param	string		Edit params (Syntax: &edit[...] for alt_doc.php)
	 * @param	string		New element params (Syntax: &edit[...] for alt_doc.php)
	 * @return	string		HTML table
	 */
	function tt_content_drawColHeader($colName,$editParams,$newParams)	{

		if (t3lib_div::compat_version('4.4')) {
			return $this->tt_content_drawColHeader_44($colName, $editParams, $newParams);
		}

			// Create header row:
		$out = array();
		$out['header'] = '
				<tr>
					<td class="bgColor2" nowrap="nowrap"><img src="clear.gif" width="1" height="2" alt="" /><br /><div align="center"><b>' . htmlspecialchars($GLOBALS['LANG']->csConvObj->conv_case($GLOBALS['LANG']->charSet, $colName, 'toUpper')) . '</b></div><img src="clear.gif" width="1" height="2" alt="" /></td>
				</tr>';

			// Create command links:
		if ($this->tt_contentConfig['showCommands'])	{
				// Start cell:
			$out['control_top'] = '
				<tr>
					<td class="bgColor5">';

				// Edit whole of column:
			if ($editParams)	{
				$out['edit'] ='<a href="#" onclick="'.htmlspecialchars(t3lib_BEfunc::editOnClick($editParams,$this->backPath)).'">'.
						'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/edit2.gif','width="11" height="12"').' title="'.$GLOBALS['LANG']->getLL('editColumn',1).'" alt="" />'.
						'</a>';
			}
				// New record:
			if ($newParams)	{
				$out['new'] ='<a href="#" onclick="'.htmlspecialchars($newParams).'">'.
						'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/new_record.gif','width="16" height="12"').' title="'.$GLOBALS['LANG']->getLL('newInColumn',1).'" alt="" />'.
						'</a>';
			}
				// End cell:
			$out['control_bottom'] = '
					</td>
				</tr>';
		}

		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['tt_content_drawColHeader'])) {
			$_params = array('tt_content',$row['uid']);
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['tt_content_drawColHeader'] as $_funcRef) {
				$_params = array(
					'content' => &$out,
					'colName' => $colName,
					'editParams' => $editParams,
					'newParams' => $newParams,
				);
				t3lib_div::callUserFunction($_funcRef,$_params,$this);
			}
		}

			// Wrap and return:
		return '
			<table border="0" cellpadding="0" cellspacing="0" width="100%" class="typo3-page-colHeader">'.($space?'
				<tr>
					<td><img src="clear.gif" height="'.$space.'" alt="" /></td>
				</tr>':'').
				implode('', $out).'
			</table>';
	}

	/**
	 * Draw header for a content element column: (Version from T3 4.4.0)
	 *
	 * @param	string		Column name
	 * @param	string		Edit params (Syntax: &edit[...] for alt_doc.php)
	 * @param	string		New element params (Syntax: &edit[...] for alt_doc.php)
	 * @return	string		HTML table
	 */
	function tt_content_drawColHeader_44($colName,$editParams,$newParams)	{

		$icons = array();
		$icons['control_top'] = '<div class="t3-page-colHeader-icons">';

			// Create command links:
		if ($this->tt_contentConfig['showCommands']) {
				// New record:
			if ($newParams) {
				$icons['new'] .= '<a href="#" onclick="' . htmlspecialchars($newParams) . '" title="' . $GLOBALS['LANG']->getLL('newInColumn', TRUE) . '">' .
					t3lib_iconWorks::getSpriteIcon('actions-document-new') .
					'</a>';
			}
				// Edit whole of column:
			if ($editParams) {
				$icons['edit'] = '<a href="#" onclick="' . htmlspecialchars(t3lib_BEfunc::editOnClick($editParams, $this->backPath)) . '" title="' . $GLOBALS['LANG']->getLL('editColumn', TRUE) . '">' .
					t3lib_iconWorks::getSpriteIcon('actions-document-open') .
					'</a>';
			}
		}
		if (count($icons)) {
			$icons['control_bottom'] = '</div>';
		} else {
			unset($icons['control_top']);
		}

		$icons['header'] = '<div class="t3-page-colHeader-label">' . htmlspecialchars($colName) . '</div>';

		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['tt_content_drawColHeader'])) {
			$_params = array('tt_content',$row['uid']);
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['tt_content_drawColHeader'] as $_funcRef) {
				$_params = array(
					'content' => &$icons,
					'colName' => $colName,
					'editParams' => $editParams,
					'newParams' => $newParams,
				);
				t3lib_div::callUserFunction($_funcRef,$_params,$this);
			}
		}

			// Create header row:
		$out = '<div class="t3-page-colHeader t3-row-header">
					' . implode('', $icons) . '
				</div>';
		return $out;
	}

}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['kb_nescefe/class.ux_tx_cms_layout.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['kb_nescefe/class.ux_tx_cms_layout.php']);
}

?>
