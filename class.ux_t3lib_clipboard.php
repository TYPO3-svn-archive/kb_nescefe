<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2004-2010 Bernhard Kraft (kraftb@think-open.at)
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
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * Extending the clipboard class so content elements can get pasted to specific columns (colPos)
 *
 * $Id$
 *
 * @author	Bernhard Kraft <kraftb@think-open.at>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 */


class ux_t3lib_clipboard extends t3lib_clipboard {

	/**
	 * pasteUrl of the element (database and file)
	 * For the meaning of $table and $uid, please read from ->makePasteCmdArray!!!
	 * The URL will point to tce_file or tce_db depending in $table
	 *
	 * @param	string		Tablename (_FILE for files)
	 * @param	mixed		"destination": can be positive or negative indicating how the paste is done (paste into / paste after)
	 * @param	boolean		If set, then the redirect URL will point back to the current script, but with CB reset.
	 * @return	string
	 */
	function pasteUrl($table,$uid,$setRedirect=1,$colPos = 0, $sys_language_uid = 0, $parentPosition = '') {
		$rU = $this->backPath.($table=='_FILE'?'tce_file.php':'tce_db.php').'?'.
			($setRedirect ? 'redirect='.rawurlencode(t3lib_div::linkThisScript(array('CB'=>''))) : '').
			'&vC='.$GLOBALS['BE_USER']->veriCode().
			'&prErr=1&uPT=1'.
			'&CB[paste]='.(($table=='tt_content')?rawurlencode($table.'|'.$uid.'|'.$colPos.'|'.$sys_language_uid.'|'.$parentPosition):rawurlencode($table.'|'.$uid)).
			'&CB[pad]='.$this->current;
		return $rU;
	}

	/**
	 * Applies the proper paste configuration in the $cmd array send to tce_db.php.
	 * $ref is the target, see description below.
	 * The current pad is pasted
	 *
	 * 		$ref: [tablename]:[paste-uid].
	 * 		tablename is the name of the table from which elements *on the current clipboard* is pasted with the 'pid' paste-uid.
	 * 		No tablename means that all items on the clipboard (non-files) are pasted. This requires paste-uid to be positive though.
	 * 		so 'tt_content:-3'	means 'paste tt_content elements on the clipboard to AFTER tt_content:3 record
	 * 		'tt_content:30'	means 'paste tt_content elements on the clipboard into page with id 30
	 * 		':30'	means 'paste ALL database elements on the clipboard into page with id 30
	 * 		':-30'	not valid.
	 *
	 * @param	string		[tablename]:[paste-uid], see description
	 * @param	array		Command-array
	 * @return	array		Modified Command-array
	 */
	function makePasteCmdArray($ref,$CMD) {
		list($pTable, $pUid, $pColPos, $sys_language_uid_str, $parentPosition) = explode('|', $ref);
		$sys_language_uid = intval($sys_language_uid_str);
		$pUid = intval($pUid);

		if ($pTable || ($pUid>=0)) {	// pUid must be set and if pTable is not set (that means paste ALL elements) the uid MUST be positive/zero (pointing to page id)
			$elements = $this->elFromTable($pTable);

			$elements = array_reverse($elements);	// So the order is preserved.
			$mode = ($this->currentMode()=='copy') ? 'copy' : 'move';

				// Traverse elements and make CMD array
			reset($elements);
			while (list($tP) = each($elements)) {
				list($table, $uid) = explode('|', $tP);
				if (!is_array($CMD[$table])) {
					$CMD[$table]=array();
				}
				$CMD[$table][$uid][$mode] = $pUid;
				if ($mode=='move') {
					$this->removeElement($tP);
				}
				if ($pUid>0) {
						// Move onto a page. Fix colPos ans sys_language_uid
					if (($mode=='copy') && ($table=='tt_content')) {
						$tce = t3lib_div::makeInstance('t3lib_TCEmain');
						$tce->start(Array(), $CMD);
						if (strlen($sys_language_uid_str)) {
							$tce->setChildsToLang = $sys_language_uid;
						}
						$tce->process_cmdmap();
						$CMD = '';
						$dataArray = Array();
						$newUid = $tce->copyMappingArray[$table][$uid];
						if (strlen($pColPos)) {
							$dataArray['tt_content'][$newUid]['colPos'] = $pColPos;
						}
						if (strlen($sys_language_uid_str)) {
							$dataArray['tt_content'][$newUid]['sys_language_uid'] = $sys_language_uid;
						}
						$dataArray['tt_content'][$newUid]['parentPosition'] = $parentPosition;
						$tce = t3lib_div::makeInstance('t3lib_TCEmain');
						$tce->start($dataArray, Array());
						$tce->process_datamap();
						$sortInfo = $tce->getSortNumber('tt_content', $newUid, $pUid);
						if ($sortInfo !== false) {
							$sortRow = $GLOBALS['TCA']['tt_content']['ctrl']['sortby'];
							$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tt_content', 'uid='.$newUid, array($sortRow => $sortInfo));
						}
					} elseif ($table=='tt_content') {
						$dataArray = Array();
						if (strlen($pColPos)) {
							$dataArray['tt_content'][$uid]['colPos'] = $pColPos;
						}
						if (strlen($sys_language_uid_str)) {
							$dataArray['tt_content'][$uid]['sys_language_uid'] = $sys_language_uid;
						}
						$dataArray['tt_content'][$uid]['pid'] = $pUid;
						$dataArray['tt_content'][$uid]['parentPosition'] = $parentPosition;
						$tce = t3lib_div::makeInstance('t3lib_TCEmain');
						$tce->start($dataArray, Array());
						$tce->process_datamap();

						$sortInfo = $tce->getSortNumber('tt_content', $uid, $pUid);
						if ($sortInfo !== false) {
							$sortRow = $GLOBALS['TCA']['tt_content']['ctrl']['sortby'];
							$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tt_content', 'uid='.$uid, array($sortRow => $sortInfo));
						}
						unset($CMD[$table][$uid][$mode]);
					}
				} else {
					if ($table=='tt_content') {
						$tRec = t3lib_BEfunc::getRecord('tt_content', abs($pUid));
						$tce = t3lib_div::makeInstance('t3lib_TCEmain');
						$tce->start(Array(), $CMD);
						$tce->setChildsToLang = $tRec['sys_language_uid'];
						$tce->process_cmdmap();
						$CMD = '';
						if ($mode == 'copy') {
							
						} else {
								// Moving after another record. Still have to set colPos and parentPosition correctly
							$dataArray = Array();
							$dataArray['tt_content'][$uid]['colPos'] = $tRec['colPos'];
							$dataArray['tt_content'][$uid]['parentPosition'] = $tRec['parentPosition'];
							$tce = t3lib_div::makeInstance('t3lib_TCEmain');
							$tce->start($dataArray, Array());
							$tce->process_datamap();
						}
					}
				}
			}
			$this->endClipboard();
		}
		return $CMD;
	}

}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['kb_nescefe/class.ux_t3lib_clipboard.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['kb_nescefe/class.ux_t3lib_clipboard.php']);
}

?>
