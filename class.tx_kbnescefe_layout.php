<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010 Bernhard Kraft (kraftb@think-open.at)
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

class tx_kbnescefe_layout {


	/*
	 * Constructor: This takes care of stuff required when kb_nescefe is active in the page module
	 *
	 */
	function tx_kbnescefe_layout() {
		if (!$GLOBALS['SOBE']->doc->inDocStylesArray['kb_nescefe']) {
			$styleFile = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kb_nescefe']['beStyles'];
			$styleFile = t3lib_div::getFileAbsFileName($styleFile);
			if (file_exists($styleFile)) {
				$GLOBALS['SOBE']->doc->inDocStylesArray['kb_nescefe'] = t3lib_div::getURL($styleFile);
			}
		}
	}


	function initClipboard() {
		$this->clipObj = t3lib_div::makeInstance('t3lib_clipboard');		// Start clipboard
		$this->clipObj->backPath = $GLOBALS['BACK_PATH'];
		$this->clipObj->initializeClipboard();	// Initialize - reads the clipboard content from the user session

			// Clipboard actions are handled:
		$CB = t3lib_div::_GET('CB');	// CB is the clipboard command array
		$CB['setP']='normal';	// If the clipboard is NOT shown, set the pad to 'normal'.
		$this->clipObj->setCmd($CB);		// Execute commands.
		$this->clipObj->cleanCurrent();	// Clean up pad
		$this->clipObj->endClipboard();	// Save the clipboard content
	}

	function drawHeader($params, &$parentObject) {
		$this->parentObject = &$parentObject;
		$keys = array_keys($params['content']);
		$pasteLink = $this->getPasteLink($params['row']);
		$copyLink = $this->getCopyCutLink($params['row'], 'copy');
		$cutLink = $this->getCopyCutLink($params['row'], 'cut');
		$this->insertIntoArray($params['content'], array('edit', 'control_top'), array(), 'pasteLink', $pasteLink);
		$this->insertIntoArray($params['content'], array('move_wrap_begin', 'move_down', 'move_up', 'new', 'edit', 'control_top'), array('move_wrap_begin'), 'cutLink', $cutLink);
		$this->insertIntoArray($params['content'], array('move_wrap_begin', 'move_down', 'move_up', 'new', 'edit', 'control_top'), array('move_wrap_begin'), 'copyLink', $copyLink);
	}

	function drawColHeader($params, &$parentObject) {
		$this->parentObject = &$parentObject;
		if (preg_match('/[&\?]colPos=([0-9]+)(&|$)/', $params['newParams'], $matches)==1) {
			$colPos = $matches[1];
			$slID = 0;
			if (preg_match('/[&\?]sys_language_uid=([0-9]+)(&|$)/', $params['newParams'], $langMatches)==1) {
				$slID = intval($langMatches[1]);
			}
			$parentPosition = '';
			if (preg_match('/[&\?]parentPosition=([0-9a-zA-Z_\:]+)(&|$)/', $params['newParams'], $parentPosMatches)==1) {
				$parentPosition = $parentPosMatches[1];
			}
			$pasteLink = $this->getPasteLink(array(), $colPos, $slID, $parentPosition, true);
			$this->insertIntoArray($params['content'], array('new', 'edit', 'control_top'), array(), 'pasteLink', $pasteLink);
		}
		
	}

	function insertIntoArray(&$array, $position, $beforeKeys, $key, $value) {
		$currentKeys = array_keys($array);
		list($afterKey) = array_intersect($position, $currentKeys);
		if ($afterKey) {
			$pos = array_search($afterKey, $currentKeys);
			$addOffset = in_array($afterKey, $beforeKeys)?0:1;
			$pre = array_slice($array, 0, $pos+$addOffset, true);
			$post = array_slice($array, $pos+$addOffset, NULL, true);
			$new = array($key => $value);
			$array = array_merge($pre, $new, $post);
		} else {
			$array[$key] = $value;
		}
	}

	function getPasteLink($row, $colPos = 0, $sys_language_uid = 0, $parentPosition = '', $header = false) {
		global $LANG;
		if (!($this->clipObj && method_exists($this->clipObj, 'elFromTable'))) {
			$this->initClipboard();
		}
		$elFromTable = $this->clipObj->elFromTable('tt_content');
		if (count($elFromTable)) {
			$recursiveNestingDetected = false;
			$moveAfterSelf = false;
			$parentContainer = false;
			if ($parentPosition || $row['parentPosition']) {
				list($parentContainer) = explode('__', $parentPosition?$parentPosition:$row['parentPosition']);
			}
			foreach ($elFromTable as $element => $type) {
				list($elementTable, $elementUid) = explode('|', $element);
				$elementUid = intval($elementUid);
				if ($elementUid == $row['uid']) {
					if ($type == '1o') {
						$moveAfterSelf = true;
						break;
					}
				}
				if ($parentContainer) {
					$recursiveNestingDetected |= $this->findRecursiveNesting($parentContainer, $elementUid);
					if ($recursiveNestingDetected) {
						break;
					}
				}
			}
			if (!($recursiveNestingDetected || $moveAfterSelf)) {
				if (t3lib_div::compat_version('4.4')) {
					return '<a href="'.htmlspecialchars($this->clipObj->pasteUrl('tt_content',$row['uid']?-$row['uid']:$this->parentObject->id, 1, $colPos, $sys_language_uid, $parentPosition)).'" onclick="'.htmlspecialchars('return '.$this->clipObj->confirmMsg('tt_content',$row,'after', $elFromTable)).'" title="' . $LANG->sL('LLL:EXT:lang/locallang_mod_web_list.xml:clip_paste'.($header?'Into':'After'), TRUE) . '">' . t3lib_iconWorks::getSpriteIcon('actions-document-paste-'.($header?'into':'after')) . '</a>';
				} else {
					return '&nbsp; <a href="'.htmlspecialchars($this->clipObj->pasteUrl('tt_content',$row['uid']?-$row['uid']:$this->parentObject->id, 1, $colPos, $sys_language_uid, $parentPosition)).'" onclick="'.htmlspecialchars('return '.$this->clipObj->confirmMsg('tt_content',$row,$header?'into':'after', $elFromTable)).'"><img'.t3lib_iconWorks::skinImg($this->parentObject->backPath,'gfx/clip_paste'.($header?'into':'after').'.gif','width="12" height="12"').' title="'.$GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_mod_web_list.php:clip_paste'.($header?'Into':'After'), TRUE).'" alt="" /></a> &nbsp;';
				}
			}
		}
		return '';
	}

	function findRecursiveNesting($containerUid, $elementUid) {
		$containerUid = intval($containerUid);
		if ($containerUid) {
			if ($containerUid == $elementUid) {
				return true;
			}
			$containerRec = t3lib_BEfunc::getRecord('tt_content', $containerUid);
			if ($containerRec['parentPosition']) {
				list($parentContainer) = explode('__', $containerRec['parentPosition']);
				return $this->findRecursiveNesting($parentContainer, $elementUid);
			}
		}
		return false;
	}
	
	function getCopyCutLink($row, $mode)	{
		global $LANG;
		if (!($this->clipObj&&method_exists($this->clipObj, 'elFromTable')))	{
			$this->initClipboard();
		}
		$isSel = $this->clipObj->isSelected('tt_content', $row['uid']);
		if (t3lib_div::compat_version('4.4')) {
			$sprite = 'actions-edit-'.$mode.($mode==$isSel?'-release':'');
			return '<a href="'.$this->clipObj->selUrlDB('tt_content', $row['uid'], (($mode=='copy')||($isSel==$mode))?1:0, $isSel==$mode?1:0).'o" title="' . $LANG->sL('LLL:EXT:lang/locallang_core.php:cm.' . $mode, TRUE) . '">' . t3lib_iconWorks::getSpriteIcon($sprite) . '</a>';
		} else {
			return ' <a href="'.$this->clipObj->selUrlDB('tt_content', $row['uid'], (($mode=='copy')||($isSel==$mode))?1:0, $isSel==$mode?1:0).'o" title="' . $LANG->sL('LLL:EXT:lang/locallang_core.php:cm.' . $mode, TRUE) . '"><img'.t3lib_iconWorks::skinImg($this->parentObject->backPath,'gfx/clip_'.$mode.($mode==$isSel?'_h':'').'.gif','width="12" height="12"').' title="'.$GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_mod_file_list.php:clip_'.$mode,1).'" alt="" /></a> ';
		}
	}

}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['kb_nescefe/class.tx_kbnescefe_layout.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['kb_nescefe/class.tx_kbnescefe_layout.php']);
}

?>
