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
		// KB_NESCEFE CHANGES ----- BEGIN
	function pasteUrl($table,$uid,$setRedirect=1,$colPos = 0, $sys_language_uid = 0, $parentPosition = '') {
		// KB_NESCEFE CHANGES -----END 
		$rU = $this->backPath.($table=='_FILE'?'tce_file.php':'tce_db.php').'?'.
			($setRedirect ? 'redirect='.rawurlencode(t3lib_div::linkThisScript(array('CB'=>''))) : '').
			'&vC='.$GLOBALS['BE_USER']->veriCode().
			'&prErr=1&uPT=1'.
				// KB_NESCEFE CHANGES ----- BEGIN
			'&CB[paste]='.(($table=='tt_content')?rawurlencode($table.'|'.$uid.($uid>=0?('|'.$colPos.'|'.$sys_language_uid.'|'.$parentPosition):'')):rawurlencode($table.'|'.$uid)).
				// KB_NESCEFE CHANGES ----- END
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
	function makePasteCmdArray($ref,$CMD)	{
		list($pTable, $pUid, $pColPos, $sys_language_uid_str, $parentPosition) = explode('|', $ref);
		$sys_language_uid = intval($sys_language_uid_str);
		$pUid = intval($pUid);

		if ($pTable || $pUid>=0)	{	// pUid must be set and if pTable is not set (that means paste ALL elements) the uid MUST be positive/zero (pointing to page id)
			$elements = $this->elFromTable($pTable);

			$elements = array_reverse($elements);	// So the order is preserved.
			$mode = $this->currentMode()=='copy' ? 'copy' : 'move';

				// Traverse elements and make CMD array
			foreach ($elements as $tP => $value) {
				list($table,$uid) = explode('|',$tP);
				if (!is_array($CMD[$table]))	$CMD[$table]=array();
					// KB_NESCEFE CHANGES ----- BEGIN
					// Now keep the clipboard fix small and move all handling to t3lib_tcemain
				$CMD[$table][$uid][$mode]=$pUid.($pUid<0?'':(','.$pColPos.','.$sys_language_uid.','.$parentPosition));
					// KB_NESCEFE CHANGES ----- END 
				if ($mode=='move')	$this->removeElement($tP);
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
