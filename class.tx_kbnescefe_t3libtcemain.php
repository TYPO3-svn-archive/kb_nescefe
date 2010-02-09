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
 * t3lib_tcemain hook
 *
 * @author	Bernhard Kraft <kraftb@think-open.at>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 */


class tx_kbnescefe_t3libtcemain	{

	function processMove($table, $uid, $destPid, $moveRec, $updateFields, $pObj, $origDestPid = 0) {
		$attachedRecords = t3lib_BEfunc::getRecordsByField('tt_content', 'pid', $moveRec['pid'], ' AND CType=\'list\' AND list_type=\'kb_nescefe_pi1\' AND parentPosition LIKE \''.$uid.'__%\'');
		if (is_array($attachedRecords)) {
			$mRec = t3lib_BEfunc::getRecord('tt_content', $uid);
			$cmd = array();
			$data = array();
			$destRec = t3lib_BEfunc::getRecord('tt_content', $destPid);
			foreach ($attachedRecords as $row) {
				$cmd['tt_content'][$row['uid']]['move'] = $destPid;
				if ($origDestPid>=0) {
					$data['tt_content'][$row['uid']]['sys_language_uid'] = $mRec['sys_language_uid'];
				} else {
					$aRec = t3lib_BEfunc::getRecord('tt_content', abs($origDestPid));
					$data['tt_content'][$row['uid']]['sys_language_uid'] = $aRec['sys_language_uid'];
				}
			}
			$localTCE = clone($pObj);
			$localTCE->start($data, $cmd);
			$localTCE->process_datamap();
			$localTCE->process_cmdmap();
		}
	}

	function moveRecord_firstElementPostProcess($table, $uid, $destPid, $moveRec, $updateFields, &$pObj)	{
		if (($table=='tt_content'))	{
			$this->processMove($table, $uid, $destPid, $moveRec, $updateFields, $pObj);
		}
	}

	function moveRecord_afterAnotherElementPostProcess($table, $uid, $destPid, $origDestPid, $moveRec, $updateFields, &$pObj)	{
		if ($table=='tt_content')	{
			$this->processMove($table, $uid, $destPid, $moveRec, $updateFields, $pObj, $origDestPid);
		}
	}

	function processCopy($origUid, $newUid, &$pObj)	{
		$origRec = t3lib_BEfunc::getRecord('tt_content', $origUid);
		$newRec = t3lib_BEfunc::getRecord('tt_content', $newUid);
		$attachedRecords = t3lib_BEfunc::getRecordsByField('tt_content', 'pid', $origRec['pid'], ' AND parentPosition LIKE \''.$origUid.'__%\'');
		$cmd = array();
		$rowArr = array();
		if (is_array($attachedRecords))	{
			foreach ($attachedRecords as $row) {
				$rowArr[$row['uid']] = $row;
				$cmd['tt_content'][$row['uid']]['copy'] = $newRec['pid'];
			}
		}
		if (count($cmd)) {
			$localTCE = clone($pObj);
			$localTCE->copyMappingArray = array();
			$localTCE->start(array(), $cmd);
			$localTCE->process_cmdmap();
			$data = array();
			foreach ($rowArr as $uid => $row) {
				$copyUid = $localTCE->copyMappingArray_merged['tt_content'][$row['uid']];
				if ($newUid) {
					$parts = explode('__', $row['parentPosition'], 2);
					$newParentPosition = $newUid.'__'.$parts[1];
					$data['tt_content'][$copyUid]['parentPosition'] = $newParentPosition;
					if (isset($pObj->setChildsToLang)) {
						$data['tt_content'][$copyUid]['sys_language_uid'] = $pObj->setChildsToLang;
					}
				}
			}
			$localTCE->start($data, array());
			$localTCE->process_datamap();
		}
	}

	function processDelete($id, $pObj) {
		$attachedRecords = t3lib_BEfunc::getRecordsByField('tt_content', 'deleted', 0, ' AND parentPosition LIKE \''.$id.'__%\'');
		if (is_array($attachedRecords)) {
			$cmd = array();
			foreach ($attachedRecords as $row) {
				$cmd['tt_content'][$row['uid']]['delete'] = 1;
			}
			$localTCE = $pObj;
			$localTCE->copyMappingArray = array();
			$localTCE->start(array(), $cmd);
			$localTCE->process_cmdmap();
		}
	}

	function processCmdmap_postProcess($command, $table, $id, $value, &$pObj) {
		if (($command=='copy') && ($table=='tt_content') && $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kb_nescefe']['copyRecursive']) {
			if ($newId = $pObj->copyMappingArray['tt_content'][$id]) {
				$this->processCopy($id, $newId, $pObj);
			}
		}
		if (($command=='delete')&&($table=='tt_content')) {
			$this->processDelete($id, $pObj);
		}
	}

	function processDatamap_postProcessFieldArray($command, $table, $id, $value, &$pObj) {
	}

}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['kb_nescefe/class.tx_kbnescefe_t3libtcemain.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['kb_nescefe/class.tx_kbnescefe_t3libtcemain.php']);
}

?>
