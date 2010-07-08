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
 * Softref parser for elements nested inside nescefe containers
 *
 * @author	Bernhard Kraft <kraftb@think-open.at>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 */



require_once(PATH_t3lib.'class.t3lib_softrefproc.php');

class tx_kbnescefe_softrefs extends t3lib_softrefproc {

		// Internal:
	var $tokenID_basePrefix = '';
 
	/**
	 * Main function for finding kb_nescefe parentPointer softreferences
	 *
	 * @param	string		Database table name
	 * @param	string		Field name for which processing occurs
	 * @param	integer		UID of the record
	 * @param	string		The content/value of the field
	 * @param	string		The softlink parser key. This is only interesting if more than one parser is grouped in the same class. That is the case with this parser.
	 * @param	array		Parameters of the softlink parser. Basically this is the content inside optional []-brackets after the softref keys. Parameters are exploded by ";"
	 * @param	string		If running from inside a FlexForm structure, this is the path of the tag.
	 * @return	array		Result array on positive matches, see description in class.t3lib_softrefproc.php. Otherwise false
	 * @see t3lib/class.t3lib_softrefproc.php
	 */
	function findRef($table, $field, $uid, $content, $spKey, $spParams, $structurePath='')	{
		$this->tokenID_basePrefix = $table.':'.$uid.':'.$field.':'.$structurePath.':'.$spKey;
		$retVal = FALSE;

		if ($table === 'tt_content') {
			$record = t3lib_BEfunc::getRecord($table, $uid);
			switch($spKey)	{
				case 'kb_nescefe_parent':
					if ($record['parentPosition']) {
						$retVal = $this->findRef_parentPointer($content, $spParams);
					}
				break;
				case 'kb_nescefe_column':
					if (intval($record['colPos']) === $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kb_nescefe']['containerElementColPos']) {
//						return parent::findRef($table, $field, $uid, $content, 'substitute', $spParams, $structurePath);
						return parent::findRef($table, $field, $uid, $content, 'notify', $spParams, $structurePath);
					}
				break;
				case 'kb_nescefe_container':
					if (($record['CType'] === 'list') && ($record['list_type'] === 'kb_nescefe_pi1')) {
						$retVal = $this->findRef_container($content, $spParams);
					}
				break;
			}
		}

		return $retVal;
	}


	/**
	 * Finding UIDs of content elements in kb_nescefe parent pointers.
	 *
	 * @param	string		The input content to analyse
	 * @param	array		Parameters set for the softref parser key in TCA/columns
	 * @return	array		Result array on positive matches, see description above. Otherwise false
	 */
	function findRef_parentPointer($content, $spParams) {
		$resultArray = FALSE;
		if (preg_match('/^([0-9]+)__[0-9_]+/', $content, $matches)) {
			$tokenValue = intval($matches[1]);
			$tokenID = $this->makeTokenID();
			$newContent = preg_replace('/^[0-9]+__/', '{softref:'.$tokenID.'}__', $content);
			$elementTitle = t3lib_BEfunc::getRecordTitle('tt_content', $tokenValue);
			$resultArray = array(
				'content' => $newContent,
				'elements' => array(
					array(
						'matchString' => $matches[0],
						'subst' => array(
							'tokenID' => $tokenID,
							'tokenValue' => $tokenValue,
							'type' => 'db',
							'recordRef' => 'tt_content:'.$tokenValue,
							'title' => $elementTitle,
						)
					)
				)
			);
		}
		return $resultArray;
	}

	/**
	 * Returning a softref configuration for a simple value fieldf
	 * Gets used for the container column (can be different on export and import system) and when enabled for the nescefe template record
	 *
	 * @param	string		The input content to analyse
	 * @param	array		Parameters set for the softref parser key in TCA/columns
	 * @return	array		Result array on positive matches, see description above. Otherwise false
	 */
	function findRef_container($content, $spParams) {
		$tokenID = $this->makeTokenID();
		return array(
			'content' => '{softref:'.$tokenID.'}',
			'elements' => array(
				array(
					'matchString' => $content,
					'subst' => array(
						'tokenID' => $tokenID,
						'tokenValue' => $content,
						'recordRef' => 'tx_kbnescefe_containers:'.$content,
						'type' => 'db',
					)
				)
			)
		);
	}

}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['kb_nescefe/class.tx_kbnescefe_softrefs.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['kb_nescefe/class.tx_kbnescefe_softrefs.php']);
}

?>
