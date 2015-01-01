<?php
namespace ThinkopenAt\KbNescefe\Hooks;

/***************************************************************
*  Copyright notice
*
*  (c) 2006-2015 Bernhard Kraft (kraftb@think-open.at)
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
 * DataHandler hook
 *
 * @author	Bernhard Kraft <kraftb@think-open.at>
 */

use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Backend\Utility\BackendUtility;

class DataHandler {

/*

Thinking "loud" ...

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
UPDATE: Of course this should be. As the interface for making a copy/localize is the process_cmdmap
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

	public static $dataMapDefaults = array();

	public function __construct() {
		$this->config = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kb_nescefe'];
	}


	/**
	 * This hook method gets called from within DataHandler BEFORE a command (copy, move, localize, etc.) is being executed.
	 *
	 * @param string $command: The command which is being executed
	 * @param string $table: The table upon which the action is taking place
	 * @param string $id: The id of the record which is being processed
	 * @param string $value: The additional value parameter of the operation (copy/move target uid, etc.)
	 * @param \TYPO3\CMS\Core\DataHandling\DataHandler $parentObject: A pointer to the parent DataHandler object
	 * @param array|FALSE $pasteUpdate: Fields which shall get updated
	 * @return void
	 */
	public function processCmdmap_preProcess($command, $table, $id, &$value, &$parentObject, &$pasteUpdate) {
		if (!(($table == 'pages') || ($table == 'tt_content'))) {
			// Guard clause
			return;
		}
		switch ($command) {
			case 'move':
				// Moving tt_content elements is handled in "moveRecord_firstElementPostProcess"
				// and "moveRecord_afterAnotherElementPostProcess"
				break;

			case 'copy':
				$currentRecord = BackendUtility::getRecord($table, $id);
				if ($table === 'pages') {
					$parentObject->copyPageInfo[$id] = 'page';
				} elseif (($currentRecord['CType'] === 'list') && ($currentRecord['list_type'] === 'kbnescefe_pi1')) {
					// Remember the container records which shall get copied
					$parentObject->copyInfo[$id] = is_array($pasteUpdate) ? $pasteUpdate : 'container';
				}
				if ($table === 'tt_content' && intval($value) < 0) {
					if (is_array($afterElement = BackendUtility::getRecord('tt_content', abs($value)))) {
						$this->sanitizePasteUpdate($pasteUpdate, $afterElement);
					}
				}
			break;

			case 'localize':
				// Implemented in "processCmdmap_postProcess"
			break;

			case 'delete':
				// "delete" is performed in the preProcess routine. So first all child elements get
				// removed and as last element the container will get deleted
				if ($table === 'tt_content') {
					$this->processDelete($id);
				}
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


	/**
	 * This hook method gets called from within DataHandler AFTER a command (copy, move, localize, etc.) has been executed.
	 *
	 * @param string $command: The command which has been executed
	 * @param string $table: The table upon which the action got performed
	 * @param string $id: The id of the record which has been being processed
	 * @param string $value: The additional value parameter of the operation (copy/move target uid, etc.)
	 * @param \TYPO3\CMS\Core\DataHandling\DataHandler $parentObject: A pointer to the parent DataHandler object
	 * @return void
	 */
	public function processCmdmap_postProcess($command, $table, $id, $value, &$parentObject) {
		if (!(($table == 'pages') || ($table == 'tt_content'))) {
			// Guard clause
			return;
		}
		$this->parentObject = &$parentObject;
		switch ($command) {

			case 'move':
				// Moving tt_content elements is handled in "moveRecord_firstElementPostProcess"
				// and "moveRecord_afterAnotherElementPostProcess"
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
					$this->remapNescefeContents(TRUE);
				}
			break;

			case 'localize':
				if ($table == 'tt_content') {
					$this->remapNescefeContents(TRUE, intval($value));
				}
			break;

			case 'delete':
				// Implemented in "processCmdmap_preProcess"
			break;

			case 'undelete':
				// "undelete" is performed in the postProcess routine. So first the container will get restored
				// and afterwards all the elements inside the container
				if ($table === 'tt_content') {
					$this->processDelete($id, 'undelete');
				}
			break;

				// Not implemented currently
			case 'inlineLocalizeSynchronize':
			case 'version':
			default:
			break;
		}
	}


	/**
	 * Sets sane values in pasteUpdate.
	 *
	 * @param array|FALSE $pasteUpdate: Fields which shall get updated
	 * @param array $afterElement: The element after which anoter element shall get moved/copied
	 * @return void
	 */
	protected function sanitizePasteUpdate(&$pasteUpdate, array $afterElement) {
		if (!is_array($pasteUpdate)) {
			$pasteUpdate = array();
		}
		$pasteUpdate['colPos'] = $afterElement['colPos'];
		$pasteUpdate['kbnescefe_parentPosition'] = $afterElement['kbnescefe_parentPosition'];
		$pasteUpdate['kbnescefe_parentElement'] = $afterElement['kbnescefe_parentElement'];
	}


	/**
	 * This hook method will get called after the commandmap has been processed
	 *
	 * @param \TYPO3\CMS\Core\DataHandling\DataHandler $parentObject: A pointer to the parent DataHandler object
	 * @return void
	 */
	public function processCmdmap_afterFinish(\TYPO3\CMS\Core\DataHandling\DataHandler $parentObject) {
		if (is_array($parentObject->moveInfo) && count($parentObject->moveInfo)) {
			foreach ($parentObject->moveInfo as $id => $param) {
				$this->sanitizeRecordValues($id);
				if ($param === 'container') {
					// When moving a container take care all elements inside get also moved
					$this->moveContainedElements($id);
				}
			}
		}

		if (is_array($this->parentObject->copyMappingArray['tt_content'])) {
			foreach ($this->parentObject->copyMappingArray['tt_content'] as $origUid => $newUid) {
				$this->sanitizeRecordValues($newUid);
			}
		}
	}

	/**
	 * This hook method will get called when a record was inserted as first element on a page/column
	 *
	 * @param string $table: The table from which a record should get moved
	 * @param int $uid: The uid of the record which has been moved
	 * @param int $destPid: The pid of the page to which the record should get moved
	 * @param array $moveRec: The record which should get moved
	 * @param array $updateFields: The updated fields and their new values
	 * @param \TYPO3\CMS\Core\DataHandling\DataHandler $parentObject: A pointer to the parent DataHandler object
	 * @return void
	 */
	public function moveRecord_firstElementPostProcess($table, $uid, $destPid, $moveRec, $updateFields, \TYPO3\CMS\Core\DataHandling\DataHandler $parentObject) {
		if ($table === 'tt_content') {
			$currentRecord = BackendUtility::getRecord('tt_content', $uid);
			$parentObject->moveInfo[$uid] = 'element';
			if (($currentRecord['CType'] === 'list') && ($currentRecord['list_type'] === 'kbnescefe_pi1')) {
				// Remember container records which have been moved
				$parentObject->moveInfo[$uid] = 'container';
			}
		}
	}

	/**
	 * This hook method will get called when a record was inserted after another tt_content element
	 *
	 * @param string $table: The table from which a record should get moved
	 * @param int $uid: The uid of the record which should get moved
	 * @param int $destPid: The pid of the page to which the record should get moved
	 * @param int $origDestPid: The original destination PID to which the record should get moved. Will be negative in this case.
	 * @param array $moveRec: The record which should get moved
	 * @param array $updateFields: The updated fields and their new values
	 * @param \TYPO3\CMS\Core\DataHandling\DataHandler $parentObject: The parent DataHandler object
	 * @return void
	 */
	public function moveRecord_afterAnotherElementPostProcess($table, $uid, $destPid, $origDestPid, $moveRec, $updateFields, \TYPO3\CMS\Core\DataHandling\DataHandler $parentObject) {
		if ($table === 'tt_content') {

			// As we are moving after another element kbnescefe parameters wont be set but have to get set at this place (aqui!)
			$updateData = array();
			if (is_array($afterElement = BackendUtility::getRecord('tt_content', abs($origDestPid)))) {
				$this->sanitizePasteUpdate($updateData, $afterElement);
			}
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tt_content', 'uid='.intval($uid), $updateData);

			// Remember container records which have been moved
			$currentRecord = BackendUtility::getRecord('tt_content', $uid);
			$parentObject->moveInfo[$uid] = 'element';
			if (($currentRecord['CType'] === 'list') && ($currentRecord['list_type'] === 'kbnescefe_pi1')) {
				$parentObject->moveInfo[$uid] = 'container';
			}
		}
	}

	/**
	 * This hook method will get called when a record was moved to after another tt_content element via Drag&Drop
	 *
	 * @param array $incomingFieldArray: Reference to the incomingFieldArray of process_datamap
	 * @param string $table: Table name of the processed record
	 * @param string $id: The UID of the processed record
	 * @param \TYPO3\CMS\Core\DataHandling\DataHandler $parentObject: The parent DataHandler object
	 * @return void
	 */
	public function processDatamap_preProcessFieldArray(array &$incomingFieldArray, $table, $id, \TYPO3\CMS\Core\DataHandling\DataHandler $parentObject) {
		if (
			$table === 'tt_content' &&
			substr($id, 0, 3) === 'NEW' &&
			$incomingFieldArray['pid'] < 0 &&
			! ( isset($incomingFieldArray['kbnescefe_parentElement']) && isset($incomingFieldArray['kbnescefe_parentPosition']) )
		) {
			$afterUid = abs($incomingFieldArray['pid']);
			$afterRecord = BackendUtility::getRecord('tt_content', $afterUid);
			$incomingFieldArray['kbnescefe_parentElement'] = $afterRecord['kbnescefe_parentElement'];
			$incomingFieldArray['kbnescefe_parentPosition'] = $afterRecord['kbnescefe_parentPosition'];
			$incomingFieldArray['colPos'] = $afterRecord['colPos'];
		}
		if (is_array(static::$dataMapDefaults[$table])) {
			$incomingFieldArray = array_replace($incomingFieldArray, static::$dataMapDefaults[$table]);
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
	 * @param boolean $copyMissingContents: If set to true contents of a container will get copied if they have not already been copied
	 * @param int $language: The language uid which should get set for new records if the currently processed command is a "localize" cmd
	 * @return void
	 */
	protected function remapNescefeContents($copyMissingContents = FALSE, $language = 0) {
		if (is_array($this->parentObject->copyMappingArray['tt_content'])) {
			foreach ($this->parentObject->copyMappingArray['tt_content'] as $origUid => $newUid) {
				$newRec = BackendUtility::getRecord('tt_content', $newUid);

				if (($newRec['CType'] == 'list') && ($newRec['list_type'] == 'kbnescefe_pi1')) {
					$this->remapNescefeContentsInContainer($origUid, $newRec, $copyMissingContents, $language);
				}
			}
		}
	}


	/**
	 * Sets sane values after a record has been copied.
	 *
	 * @param integer $uid: The uid of the tt_content record which to sanitize
	 * @return void
	 */
	protected function sanitizeRecordValues($uid) {
		$uid = intval($uid);
		$record = BackendUtility::getRecord('tt_content', $uid);
		
		if (
			is_array($record) &&
			intval($record['colPos']) !== intval($this->config['containerElementColPos']) &&
			($record['kbnescefe_parentPosition'] || $record['kbnescefe_parentElement'] !== 0)
		) {
			// Reset kb_nescefe parent pointer after moving/copying an element out of a container.
			// It wouldn't hurt if those values stay set but it seems cleaner this way.
			$update['kbnescefe_parentElement'] = 0;
			$update['kbnescefe_parentPosition'] = '';
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tt_content', 'uid='.$uid, $update);
		}
	}


	/**
	 * This method retrieves all elements contained in the original of a copied kb_nescefe container (origUid) and checks if any of
	 * the contained elements also got copied. It will update the parentPosition field of the copied contents of the container to point
	 * to their new parent
	 *
	 * @param int $origUid: The original UID of the container which was copied
	 * @param array $newRec: The freshly copied container record
	 * @param boolean $copyMissingContents: If set to true contents of a container will get copied if they have not already been copied
	 * @param int $language: The language uid which should get set for new records if the currently processed command is a "localize" cmd
	 * @return void
	 */
	protected function remapNescefeContentsInContainer($origUid, $newRec, $copyMissingContents = FALSE, $language = 0) {
		$newUid = $newRec['uid'];
		$origRec = BackendUtility::getRecord('tt_content', $origUid);
		// First get all records which where contained in the original record and traverse them
		// Use "DESC" sorting as they will get copied each one and when being copied each records gets inserted "as first"
		// so copying them by default order would result in the records order being reversed
		$containedRecords = BackendUtility::getRecordsByField('tt_content', 'pid', $origRec['pid'], ' AND kbnescefe_parentElement = \'' . $origUid . '\'', '', 'sorting DESC');
		$datamap = array();
		foreach ($containedRecords as $containedRecord) {
			$containedUid = $containedRecord['uid'];
			// Check if the UID of those contained records can be found in the copyMappingArray
			// which should be the case if a whole page was copied
			if (
				!$this->parentObject->copyMappingArray['tt_content'][$containedUid] &&
				$copyMissingContents &&
				(
					($language === 0 && $this->config['copyRecursive']) ||
					($language !== 0 && $this->config['localizeRecursive'])
				)
			) {
				// If the cointained record did not get copied - which is the case when the user copied a single record
				// then now create a copy of the contents of the original container (or a localization if $language is passed)
				$this->copyMissingRecords($containedUid, $newRec, $language);
			}
			if ($copiedContentUid = $this->parentObject->copyMappingArray['tt_content'][$containedUid]) {

				// Fetch the copied record and update the parentPosition field to point to its new parent
				$copiedRecord = BackendUtility::getRecord('tt_content', $copiedContentUid);

				// It should not be necessary to set the "parentPosition" field as this will usually not change
				// $datamap['tt_content'][$copiedContentUid]['kbnescefe_parentPosition'] = $containedRecord['kbnescefe_parentPosition'];

				// Set the parentElement field to the new uid of the copied container
				$datamap['tt_content'][$copiedContentUid]['kbnescefe_parentElement'] = $newUid;

				// If the records got pasted to some other language also adjust the language flag of the contained records.
				// This is NOT a localization. Localization is handled differently - this is just a copy&paste into another language
				// This is also not tested right now (2010-07-02)
				if (is_array($copyInfo = $this->parentObject->copyInfo[$origUid]) && !$language) {
					if (isset($copyInfo['sys_language_uid'])) {
						$datamap['tt_content'][$copiedContentUid]['sys_language_uid'] = $copyInfo['sys_language_uid'];
					}
				}
			}
		}
		if (count($datamap)) {
			$localDataHandler = $this->getDataHandlerInstance();
			$localDataHandler->start($datamap, array(), $this->parentObject->BE_USER);
			$localDataHandler->process_datamap();
		}
	}


	/**
	 * When a kb_nescefe container gets copied on its own (not the whole page), then the contained elements will not get copied
	 * automatically. This method copies the missing records. When a localize operation is under way - which is mostly the same
	 * as when a record gets copied, then also localize the contained records.
	 *
	 * @param int $containedUid: The UID of the contained record which should get copied/localized
	 * @param array $newRec: The freshly copied container record
	 * @param int $language: The language uid which should get set for new records if the currently processed command is a "localize" cmd
	 * @return void
	 */
	protected function copyMissingRecords($containedUid, $newRec, $language = 0) {
		$cmdmap = array();
		if ($language) {
			$cmdmap['tt_content'][$containedUid]['localize'] = $language;
		} else {
			$cmdmap['tt_content'][$containedUid]['copy'] = $newRec['pid'];
		}
		$localDataHandler = $this->getDataHandlerInstance();
		$localDataHandler->start(array(), $cmdmap, $this->parentObject->BE_USER);
		$localDataHandler->process_cmdmap();
		foreach ($localDataHandler->copyMappingArray['tt_content'] as $oldUid => $newUid) {
			$this->parentObject->copyMappingArray['tt_content'][$oldUid] = $newUid;
		}
	}

	/**
	 * This method will move all elements contained in a kb_nescefe container along with its parent if the parent was moved
	 *
	 * @param int $uid: The uid of the record which was moved
	 * @return void
	 */
	protected function moveContainedElements($uid) {
		$movedRecord = BackendUtility::getRecord('tt_content', $uid);
		if (($movedRecord['CType'] === 'list') && ($movedRecord['list_type'] === 'kbnescefe_pi1')) {
			$attachedRecords = BackendUtility::getRecordsByField('tt_content', 'kbnescefe_parentElement',intval($uid), '', '', 'sorting DESC');
			if (is_array($attachedRecords) && count($attachedRecords)) {
				$cmd = array();
				$data = array();
				foreach ($attachedRecords as $row) {
					$cmd['tt_content'][$row['uid']]['move'] = $movedRecord['pid'];
					if (intval($row['sys_language_uid']) !== -1) {
						// Only set same language as container if current element has not set "All languages"
						$data['tt_content'][$row['uid']]['sys_language_uid'] = $movedRecord['sys_language_uid'];
					}
				}
				$localDataHandler = $this->getDataHandlerInstance();
				$localDataHandler->start($data, $cmd);
				$localDataHandler->process_cmdmap();
				if (is_array($data) && count($data)) {
					$localDataHandler->process_datamap();
				}
			}
		}
	}

	/**
	 * This method recursively deletes all elements found inside a container when the container is going to be deleted
	 *
	 * @param integer $id: The uid of the object going to be deleted
	 * @param string $func: The type of delete operation
	 * @return void
	 */
	protected function processDelete($id, $func = 'delete') {
		if ($func == 'undelete') {
			$attachedRecords = BackendUtility::getRecordsByField('tt_content', 'deleted', 1, ' AND kbnescefe_parentElement = \''.$id.'\'', '', '', '', FALSE);
		} elseif ($func == 'delete') {
			$attachedRecords = BackendUtility::getRecordsByField('tt_content', 'deleted', 0, ' AND kbnescefe_parentElement = \''.$id.'\'');
		}
		if (is_array($attachedRecords)) {
			$cmd = array();
			foreach ($attachedRecords as $row) {
				$cmd['tt_content'][$row['uid']][$func] = 1;
			}
			$localDataHandler = GeneralUtility::makeInstance('TYPO3\CMS\Core\DataHandling\DataHandler');
			$localDataHandler->start(array(), $cmd);
			$localDataHandler->process_cmdmap();
		}
	}


	/**
	 * This method returns a new DataHandler instance for interal usage
	 *
	 * @return \TYPO3\CMS\Core\DataHandling\DataHandler A pointer to a newly created and configred DataHandler instance for internal use
	 */
	protected function getDataHandlerInstance() {
		$dataHandler = GeneralUtility::makeInstance('TYPO3\CMS\Core\DataHandling\DataHandler');
		$dataHandler->stripslashes_values = 0;
		$dataHandler->copyTree = $this->parentObject->copyTree;
		$dataHandler->cachedTSconfig = $this->parentObject->cachedTSconfig;
		$dataHandler->dontProcessTransformations = 1;
		return $dataHandler;
	}

}

