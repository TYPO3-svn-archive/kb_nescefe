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


/*

Thinking "loud" ...

When a page or a content element get's copied it will always be the case that simply
a datamap operation is executed. The elements going to get copied will put into an array
and process_datamap will get called on them.

So when there is a process_datamap call for a kb_nescefe record check if there are records
on the records original page which have the records original uid at the beginning of parentPosition
field. problem: when a whole page is copied the sub-elements will get copied anyways.

Hmm. Do page-copies and element copies have to get handled differently. Of course. When copying a page
all elements on the page will get copied, and just the references have to get adjusted. But when a
single element get's copied it has to get taken care that all elements inside will also get copied.
At least when using the page module. What will happen when a container and "some" of the contained
elements get copied using the list module. It shouldn't happen that some elements get copied multiple
times. UPDATE: The result will be, that whenever a container is copied its contents will also get copied.
If some of the contents of the container are also in the clipboard they will get copied twice. The records
which will get copied in the hierarchy of copying the nescefe container will be at the correct position.
But the content elements simply copied via the clipboard will then point to a wrong container.

Hmm. Probably all copying of contained elements should get done from within the processCmdmap method.
UPDATE: Of course this should be. As the interface for making a copy/localize is the process_cmdma
method. So this method should get used for taking care of all copy-handling stuff.

There are two things which can get copied:
pages - then the processCmdmap will not get called for the elements but will get done using process_datamap
        but the copied elements will be registered in the copyMappingArray. so the processCmdmap_postProcess
        method could take care of all elements having been copied
content elements - the processCmdmap method will get called for each content element and will also get
registered in the copyMappingArray.

I guess the "copyMappingArray" is the main place which should get used for copying related/contained
elements.

Copying contained elements and setting/resetting the colPos, sys_language_uid and parentPosition parameters
should probably get separated from the copy process itself. So first all elements which should get copied
will get copied diving into the hierarchy. Afterwards all values should get set properly. 

For example colPos and sys_language_uid will not need to get changed when a page is being copied while.
But if content elements are copied and pasted into some different colPos and/or language those variables
will have to get changed










*/

	public function processCmdmap_preProcess($command, $table, $id, &$value, &$parentObject) {
		if (($table == 'pages') || ($table == 'tt_content')) {
			switch ($command) {
				case 'move':
						// When the target-value contains commas it is not only the target PID of a page or target UID of a record
						// but a value composed of target, colPos, language and parent-container position values.
						// Store the retrieved information in the moveInfo array which gets used by the move hook methods later on
						// for setting the appropriate values of the moved record(s)
					if (strpos($value, ',') !== false) {
						list($destPid, $colPos, $language, $parentPosition) = explode(',', $value);
						$value = $destPid;
						$parentObject->moveInfo[$table][$id] = array(
							'colPos' => $colPos,
							'sys_language_uid' => $language,
							'parentPosition' => $parentPosition,
						);
					}
				break;

				case 'copy':
						// The same as for the "move" command applies to the "copy" command values when regarding comma separated targets
					if (strpos($value, ',') !== false) {
						list($destPid, $colPos, $language, $parentPosition) = explode(',', $value);
						$value = $destPid;
						$parentObject->copyInfo[$table][$id] = array(
							'colPos' => $colPos,
							'sys_language_uid' => $language,
							'parentPosition' => $parentPosition,
						);
					}
						// When the target value is smaller than 0 it is a target content element after which the records going to be copied will end up
						// In this case set the copyInfo array by retrieving the settings of the record after which the current records shall get pasted
					if (intval($value) < 0) {
						$afterElement = t3lib_BEfunc::getRecord('tt_content', abs($value));
						$parentObject->copyInfo[$table][$id] = array(
							'colPos' => $afterElement['colPos'],
							'sys_language_uid' => $afterElement['sys_language_uid'],
							'parentPosition' => $afterElement['parentPosition'],
						);
					}
						// Rest implemented in "processCmdmap_postProcess"
				break;

				case 'localize':
					// Implemented in "processCmdmap_postProcess"
				break;

				case 'delete':
					// "delete" is performed in the preProcess routine. So first all child elements get
					// removed and as last element the container will get deleted
					$this->processDelete($id);
				break;

				case 'undelete':
					// Implemented in "processCmdmap_postProcess"
				break;

					// Not implemented currently:
				case 'inlineLocalizeSynchronize':
				case 'version':
				break;

			}
		}
	}

	public function processCmdmap_postProcess($command, $table, $id, $value, &$parentObject) {
		if (($table == 'pages') || ($table == 'tt_content')) {
			$this->parentObject = &$parentObject;
			switch ($command) {
				case 'move':
					// Implemented in "processCmdmap_preProcess" and other hooks
				break;
				case 'copy':
					if ($table == 'pages') {
						// A page has been copied.
						// Just take care all elements contained in kb_nescefe elements get set properly.
						// The UIDs of all copied element can be found in the copyMappingArray class variable
						$this->remapNescefeContents();
					} else {
						// Single elements get copied.
						// Take care that elements contained inside kb_nescefe elements get copied as well.
						$this->remapNescefeContents(true);
					}
				break;

				case 'localize':
					if ($table == 'tt_content') {
						// Single elements get localized.
						// Take care that elements contained inside kb_nescefe elements get localized as well.
//echo "DEBUG<br />\nLocalize not implemented currently!<br />\nLocalize : $value";
// exit();
						$this->remapNescefeContents(true, $value);
					}
				break;

				case 'delete':
					// Implemented in "processCmdmap_preProcess"
				break;

				case 'undelete':
					// "delete" is performed in the postProcess routine. So first the container will get restored
					// and afterwards all the elements inside the container
					$this->processDelete($id, 'undelete');
				break;

					// Not implemented currently
				case 'inlineLocalizeSynchronize':
				case 'version':
				default:
				break;
			}
		}
	}


	/**
	 * This hook method will get called when a record was inserted as first element on a page/column
	 *
	 * @param string	The table from which a record should get moved
	 * @param int	The uid of the record which should get moved
	 * @param int	The pid of the page to which the record should get moved
	 * @param array	The record which should get moved
	 * @param array	The updated fields and their new values
	 * @param t3lib_TCEmain	A pointer to the parent TCE object
	 * @return	void
	 */
	function moveRecord_firstElementPostProcess($table, $uid, $destPid, $moveRec, $updateFields, &$parentObject) {
		if ($table == 'tt_content') {
				// When moving a content element take care all parameters get set according to info in parentObject->moveInfo
			$this->adjustElementParams($uid, $parentObject);

				// When moving a container take care all elements inside get also moved
			$this->moveContainedElements($table, $uid, $destPid, $moveRec, $updateFields, $pObj);
		}
	}

	/**
	 * This hook method will get called when a record was inserted after another tt_content element
	 *
	 * @param string	The table from which a record should get moved
	 * @param int	The uid of the record which should get moved
	 * @param int	The pid of the page to which the record should get moved
	 * @param int	The original destination PID to which the record should get moved. Will be negative in this case.
	 * @param array	The record which should get moved
	 * @param array	The updated fields and their new values
	 * @param t3lib_TCEmain	A pointer to the parent TCE object
	 * @return	void
	 */
	function moveRecord_afterAnotherElementPostProcess($table, $uid, $destPid, $origDestPid, $moveRec, $updateFields, &$parentObject) {
		if ($table == 'tt_content') {
				// When moving a content element take care all parameters get set according to info in parentObject->moveInfo
				// As we are moving after another element parentObject->moveInfo wont be set from within the cmdmap preprocess hook
				// but has to get set at this place (aqui!)
			$afterElement = t3lib_BEfunc::getRecord('tt_content', abs($origDestPid));
			$parentObject->moveInfo['tt_content'][$uid] = array(
				'colPos' => $afterElement['colPos'],
				'sys_language_uid' => $afterElement['sys_language_uid'],
				'parentPosition' => $afterElement['parentPosition'],
			);
			$this->adjustElementParams($uid, $parentObject);

				// When moving a container take care all elements inside get also moved
			$this->moveContainedElements($table, $uid, $destPid, $moveRec, $updateFields, $pObj, $origDestPid);
		}
	}



	/*****************************************
	 *
	 * INTERNAL
	 *
	 * These methods get used by this hook class and are not public
	 *
	 ****************************************/

	/**
	 * This method checks if any of the copied records is a kb_nescefe container. If such a container is found all contained (and copied) elements will get remaped
	 *
	 * @param boolean	If set to true contents of a container will get copied if they have not already been copied
	 * @param int	The language uid which should get set for new records if the currently processed command is a "localize" cmd
	 * @return	void
	 */
	protected function remapNescefeContents($copyMissingContents = false, $language = 0) {
		if (is_array($this->parentObject->copyMappingArray['tt_content'])) {
			foreach ($this->parentObject->copyMappingArray['tt_content'] as $origUid => $newUid) {
				$newRec = t3lib_BEfunc::getRecord('tt_content', $newUid);

				// When copying a content element take care all parameters get set according to info in parentObject->copyInfo
				$this->adjustElementParams($origUid, $this->parentObject, true, $newUid);

				if (($newRec['CType'] == 'list') && ($newRec['list_type'] == 'kb_nescefe_pi1')) {
					$this->remapNescefeContentsInContainer($origUid, $newRec, $copyMissingContents, $language);
				}
			}
		}
	}


	/**
	 * This method retrieves all elements contained in the original of a copied kb_nescefe container (origUid) and checks if any of
	 * the contained elements also got copied. It will update the parentPosition field of the copied contents of the container to point
	 * to their new parent
	 *
	 * @param int	The original UID of the container which was copied
	 * @param array	The freshly copied container record
	 * @param boolean	If set to true contents of a container will get copied if they have not already been copied
	 * @param int	The language uid which should get set for new records if the currently processed command is a "localize" cmd
	 * @return	void
	 */
	function remapNescefeContentsInContainer($origUid, $newRec, $copyMissingContents = false, $language = 0) {
		$newUid = $newRec['uid'];
		$origRec = t3lib_BEfunc::getRecord('tt_content', $origUid);
			// First get all records which where contained in the original record and traverse them
		$containedRecords = t3lib_BEfunc::getRecordsByField('tt_content', 'pid', $origRec['pid'], ' AND parentPosition LIKE \'' . $origUid . '\_\_%\'' );
		$datamap = array();
		foreach ($containedRecords as $containedRecord) {
			$containedUid = $containedRecord['uid'];
				// Check if the UID of those contained records can be found in the copyMappingArray
				// which should be the case if a whole page was copied
			if (!$this->parentObject->copyMappingArray['tt_content'][$containedUid] && $copyMissingContents && $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kb_nescefe']['copyRecursive']) {
					// If the cointained record did not get copied - which is the case when the user copied a single record
					// then now create a copy of the contents of the original container (or a localization if $language is passed)
				$this->copyMissingRecords($containedUid, $newRec, $language);
			}
			if ($copiedContentUid = $this->parentObject->copyMappingArray['tt_content'][$containedUid]) {
					// Fetch the copied record and update the parentPosition field to point to its new parent
				$copiedRecord = t3lib_BEfunc::getRecord('tt_content', $copiedContentUid);
				list(,$parentColumn) = explode('__', $copiedRecord['parentPosition'], 2);
				$datamap['tt_content'][$copiedContentUid]['parentPosition'] = $newUid . '__' . $parentColumn;
					// If the records got pasted to some other language also adjust the language flag of the contained records.
					// This is NOT a localization. Localization is handled differently - this is just a copy&paste into another language
					// This is also not tested right now (2010-07-02)
				if (($copyInfo = $this->parentObject->copyInfo['tt_content'][$origUid]) && !$language) {
					$datamap['tt_content'][$copiedContentUid]['sys_language_uid'] = $copyInfo['sys_language_uid'];
				}
			}
		}
		if (count($datamap)) {
			$localTCE = &$this->getTCEinstance();
			$localTCE->start($datamap, array(), $this->parentObject->BE_USER);
			$localTCE->process_datamap();
		}
	}



	/**
	 * When a kb_nescefe container gets copied on its own (not the whole page), then the contained elements will not get copied
	 * automatically. This method copies the missing records. When a localize operation is under way - which is mostly the same
	 * as when a record gets copied, then also localize the contained records.
	 *
	 * @param int	The UID of the contained record which should get copied/localized
	 * @param array	The freshly copied container record
	 * @param int	The language uid which should get set for new records if the currently processed command is a "localize" cmd
	 * @return	void
	 */
	protected function copyMissingRecords($containedUid, $newRec, $language = 0) {
		$cmdmap = array();
		if ($language) {
			$cmdmap['tt_content'][$containedUid]['localize'] = $language;
		} else {
			$cmdmap['tt_content'][$containedUid]['copy'] = $newRec['pid'];
		}
		$localTCE = &$this->getTCEinstance();
		$localTCE->start(array(), $cmdmap, $this->parentObject->BE_USER);
		$localTCE->process_cmdmap();
		foreach ($localTCE->copyMappingArray['tt_content'] as $oldUid => $newUid) {
			$this->parentObject->copyMappingArray['tt_content'][$oldUid] = $newUid;
		}
	}


	/**
	 * This method recursivels deletes all elements found inside a container when the container is going to be deleted
	 *
	 * @param integer The uid of the object going to be deleted
	 * @param t3lib_TCEmain A pointer to the calling parent object
	 * @return	void
	 */
	protected function processDelete($id, $func = 'delete') {
		if ($func == 'undelete') {
			$attachedRecords = t3lib_BEfunc::getRecordsByField('tt_content', 'deleted', 1, ' AND parentPosition LIKE \''.$id.'\_\_%\'', '', '', '', false);
		} elseif ($func == 'delete') {
			$attachedRecords = t3lib_BEfunc::getRecordsByField('tt_content', 'deleted', 0, ' AND parentPosition LIKE \''.$id.'\_\_%\'');
		}
		if (is_array($attachedRecords)) {
			$cmd = array();
			foreach ($attachedRecords as $row) {
				$cmd['tt_content'][$row['uid']][$func] = 1;
			}
			$localTCE = t3lib_div::makeInstance('t3lib_TCEmain');
			$localTCE->start(array(), $cmd);
			$localTCE->process_cmdmap();
		}
	}


	/**
	 * This method returns a new TCE instance for interal usage
	 *
	 * @return t3lib_TCEmain	A pointer to a newly created and configred t3lib_TCEmain instance for internal use
	 */
	protected function &getTCEinstance() {
		$tce = t3lib_div::makeInstance('t3lib_TCEmain');
		$tce->stripslashes_values = 0;
       		$tce->copyTree = $this->parentObject->copyTree;
		$tce->cachedTSconfig = $this->parentObject->cachedTSconfig;
		$tce->dontProcessTransformations = 1;
		return $tce;
	}

	/**
	 * This method sets all parameters like colPos, sys_language_uid and parentPosition according to moveInfo array in parentObject
	 *
	 * @param int	The uid of the record which was moved
	 * @param t3lib_TCEmain	A pointer to the parent TCE object
	 * @return	void
	 */
	protected function adjustElementParams($uid, &$parentObject, $isCopy = false, $updateUid = 0) {
		$infoArray = $isCopy ? $parentObject->copyInfo : $parentObject->moveInfo;
		if ($updateInfo = $infoArray['tt_content'][$uid]) {
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tt_content', 'uid='.($updateUid?$updateUid:$uid), $updateInfo);
		}
		
	}

	/**
	 * This method will move all elements contained in a kb_nescefe container along with its parent if the parent gets moved
	 *
	 * @param string	The table from which a record should get moved
	 * @param int	The uid of the record which should get moved
	 * @param int	The pid of the page to which the record should get moved
	 * @param int	The original destination PID to which the record should get moved. Will be negative in this case.
	 * @param array	The record which should get moved
	 * @param array	The updated fields and their new values
	 * @param t3lib_TCEmain	A pointer to the parent TCE object
	 * @return	void
	 */
	function moveContainedElements($table, $uid, $destPid, $moveRec, $updateFields, $pObj, $origDestPid = 0) {
		$mRec = t3lib_BEfunc::getRecord('tt_content', $uid);
		if (($mRec['CType'] == 'list') && ($mRec['list_type'] == 'kb_nescefe_pi1')) {
			$attachedRecords = t3lib_BEfunc::getRecordsByField('tt_content', 'pid', $moveRec['pid'], ' AND parentPosition LIKE \''.$uid.'\_\_%\'');
			if (is_array($attachedRecords)) {
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
				$localTCE = &$this->getTCEinstance();
				$localTCE->start($data, $cmd);
				$localTCE->process_datamap();
				$localTCE->process_cmdmap();
			}
		}
	}


}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['kb_nescefe/class.tx_kbnescefe_t3libtcemain.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['kb_nescefe/class.tx_kbnescefe_t3libtcemain.php']);
}

?>
