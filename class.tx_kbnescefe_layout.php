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
	 *
	 * @return void
	 */
	public function tx_kbnescefe_layout() {
		if (!$GLOBALS['SOBE']->doc->inDocStylesArray['kb_nescefe']) {
			$styleFile = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kb_nescefe']['beStyles'];
			$styleFile = t3lib_div::getFileAbsFileName($styleFile);
			if (file_exists($styleFile)) {
				$GLOBALS['SOBE']->doc->inDocStylesArray['kb_nescefe'] = t3lib_div::getURL($styleFile);
			}
		}
	}


	/*
	 * This method initializes the clipboard object for usage in this class
	 *
	 *
	 * @return void
	 */
	public function initClipboard() {
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


	/*
	 * This hook method will get called for modifying the icons/contents of content element header
	 * The current icons get passed as reference in an array which is the first parameter.
	 * This value "content" of the array must get changed to modify the displayed icons.
	 *
	 * @param array Contains parameters like the content element row, the current icons.
	 * @param tx_cms_layout The parent class from which this hook is called
	 * @return void
	 */
	public function drawHeader($params, &$parentObject) {
		$this->parentObject = &$parentObject;
		$keys = array_keys($params['content']);
		$pasteLink = $this->getPasteLink($params['row']);
		$copyLink = $this->getCopyCutLink($params['row'], 'copy');
		$cutLink = $this->getCopyCutLink($params['row'], 'cut');
		$this->insertIntoArray($params['content'], array('edit', 'control_top'), array(), 'pasteLink', $pasteLink);
		$this->insertIntoArray($params['content'], array('move_wrap_begin', 'move_down', 'move_up', 'new', 'edit', 'control_top'), array('move_wrap_begin'), 'cutLink', $cutLink);
		$this->insertIntoArray($params['content'], array('move_wrap_begin', 'move_down', 'move_up', 'new', 'edit', 'control_top'), array('move_wrap_begin'), 'copyLink', $copyLink);
	}


	/*
	 * This hook method will get called for modifying the icons in the header of a content column
	 * The current icons get passed as reference in an array which is the first parameter.
	 * This value "content" of the array must get changed to modify the displayed icons.
	 *
	 *
	 * @param array Contains parameters like the current icons.
	 * @param tx_cms_layout The parent class from which this hook is called
	 * @return void
	 */
	public function drawColHeader($params, &$parentObject) {
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


	/*
	 * This method inserts the key/value combination passed as 4th and 5th parameter at the given position.
	 * The position is not passed as index, but as the key of another value in the passed array.
	 *
	 * Usually the inserted value is inserted AFTER the first "position" key which is found to match an key in the array.
	 *
	 * When the key after which the element should get inserted is also found in the third parameter "beforeKeys"
	 * then the new value should not get inserted after the found key/value but before.
	 *
	 * If no matching key is found the new value is appended at the end of the passed array.
	 *
	 *
	 * @param array The array in which to insert a new value (passed as reference)
	 * @param array An array with keys. One of the values in this array should match a key in the passed array (argument 1)
	 * @param array When the key after which the new element will get inserted is also found in this array it will be inserted before instead
	 * @param string The key of the new array element to insert
	 * @param mixed The value of the new array element to insert
	 * @return void
	 */
	protected function insertIntoArray(&$array, $position, $beforeKeys, $key, $value) {
		$currentKeys = array_keys($array);
		list($afterKey) = array_intersect($position, $currentKeys);
		if ($afterKey) {
			$pos = array_search($afterKey, $currentKeys);
			$addOffset = in_array($afterKey, $beforeKeys)?0:1;
			$pre = array_slice($array, 0, $pos+$addOffset, true);
			$post = array_slice($array, $pos+$addOffset, count($array), true);
			$new = array($key => $value);
			$array = array_merge($pre, $new, $post);
		} else {
			$array[$key] = $value;
		}
	}


	/*
	 * This method returns a link which can get used to paste an element from the clipboard
	 *
	 *
	 * @param array The row after which the elements from the clipboard should get pasted
	 * @param integer The column into which the elements from the clipboard should get pasted
	 * @param integer The language into wich the elements from the clipboard should get pasted
	 * @param string The parent container uid and column (parent pointer) into which the elements from the clipboard should get pasted
	 * @param boolean Wheter the paste link will get used for a column header
	 * @return string A complete paste link with icon wrapped in <a> tag
	 */
	protected function getPasteLink($row, $colPos = 0, $sys_language_uid = 0, $parentPosition = '', $header = false) {
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
				$target = $row['uid']?-$row['uid']:$this->parentObject->id;
				$pasteUrl = $this->clipObj->pasteUrl('tt_content', $target, 1, $colPos, $sys_language_uid, $parentPosition);
				$pasteUrl = htmlspecialchars($pasteUrl);
				if (!$row['uid']) {
					if ($parentPosition) {
						list($parentUid, $parentColumn) = explode('__', $parentPosition);
						$parentRecord = t3lib_BEfunc::getRecord('tt_content', $parentUid);
						$parentTitle = t3lib_BEfunc::getRecordTitle('tt_content', $parentRecord, true);
						$columnLabel = $LANG->sL('LLL:EXT:kb_nescefe/locallang.xml:column');
						$columnName = str_replace('###IDX###', $parentColumn+1, $columnLabel);
						$column = $parentTitle.': '.$columnName;
					} else {
						$label = $this->findColumnLabel($colPos);
						$columnLabel = $LANG->sL('LLL:EXT:kb_nescefe/locallang.xml:column');
						$columnName = $LANG->sL($label);
						$column = str_replace('###IDX###', $columnName, $columnLabel);
					}
					$confirmRow = array('header' => $column);
				} else {
					$confirmRow = $row;
				}
				$confirmMsg = 'return '.$this->clipObj->confirmMsg('tt_content', $confirmRow, $header?'into':'after', $elFromTable);
				$confirmMsg = htmlspecialchars($confirmMsg);
				if (t3lib_div::compat_version('4.4')) {
					if ($parentPosition) {
						$label = $LANG->sL('LLL:EXT:lang/locallang_mod_web_list.xml:clip_paste', TRUE);
					} else {
						$label = $LANG->sL('LLL:EXT:lang/locallang_mod_web_list.xml:clip_paste'.($header?'Into':'After'), TRUE);
					}
					$icon = t3lib_iconWorks::getSpriteIcon('actions-document-paste-'.($header?'into':'after'));
					return '<a href="'.$pasteUrl.'" onclick="'.$confirmMsg.'" title="'.$label.'">'.$icon.'</a>';
				} else {
					if ($parentPosition) {
						$label = $LANG->sL('LLL:EXT:lang/locallang_mod_web_list.php:clip_paste', TRUE);
					} else {
						$label = $LANG->sL('LLL:EXT:lang/locallang_mod_web_list.php:clip_paste'.($header?'Into':'After'), TRUE);
					}
					$imgPath = 'gfx/clip_paste'.($header?'into':'after').'.gif';
					$imgSize = 'width="12" height="12"';
					$imgSrc = t3lib_iconWorks::skinImg($this->parentObject->backPath, $imgPath, $imgSize);
					$icon = '<img '.$imgSrc.' title="'.$label.'" alt="'.$label.'" />';
					return '&nbsp; <a href="'.$pasteUrl.'" onclick="'.$confirmMsg.'">'.$icon.'</a> &nbsp;';
				}
			}
		}
		return '';
	}


	/*
	 * This method retrieves the label for a content column
	 *
	 *
	 * @param integer The column for which the localized label should get retrieved
	 * @return string The label of the column passed
	 */
	protected function findColumnLabel($colPos) {
		t3lib_div::loadTCA('tt_content');
		$items = $GLOBALS['TCA']['tt_content']['columns']['colPos']['config']['items'];
		$label = '';
		if (is_array($items)) {
			foreach ($items as $item) {
				if ($item[1] == $colPos) {
					$label = $item[0];
					break;
				}
			}
		}
		return $label;
	}


	/*
	 * Checks wheter the element whose uid is passed as second argument
	 * is not found to be a parent of the element whose uid is passed first argument.
	 *
	 * This method is used for ensuring that an element (container) can not get pasted into it's own columns.
	 * So the element whose uid is passed as second argument is not already in the container passed as
	 * first argument, but a paste link for this should get generated. If this method returns true (a recursion
	 * would occur) no paste link is generated.
	 *
	 *
	 * @param integer The UID of a an element for which to check the parents (container root line)
	 * @param integer The UID of a content element to look for in the container root line
	 * @return boolean Returns true if a recursion would be caused
	 */
	protected function findRecursiveNesting($containerUid, $elementUid) {
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


	/*
	 * This returns a icon wrapped with a link for doing a "cut" or "copy" of an element to the clipboard
	 *
	 *
	 * @param array The content element row for which to genereate the copy or cut link
	 * @param string Wheter a copy or cut link should get created. Can contain either "copy" or "cut"
	 * @return string The copy or cut icon wrapped in an <a> tag
	 */
	protected function getCopyCutLink($row, $mode)	{
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
