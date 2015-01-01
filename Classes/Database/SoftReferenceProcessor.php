<?php
namespace ThinkopenAt\KbNescefe\Database;

/***************************************************************
*  Copyright notice
*
*  (c) 2010-2015 Bernhard Kraft (kraftb@think-open.at)
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

use \TYPO3\CMS\Backend\Utility\BackendUtility;


class SoftReferenceProcessor extends \TYPO3\CMS\Core\Database\SoftReferenceIndex {

	/**
	 * @var string The prefix used for tokenIDs
	 */
	public $tokenID_basePrefix = '';
 
	/**
	 * Main function for finding kb_nescefe parent element softreferences
	 *
	 * @param string $table: Database table name
	 * @param string $field: Field name for which processing occurs
	 * @param integer $uid: UID of the record
	 * @param string $content: The content/value of the field
	 * @param string $spKey: The softlink parser key. This is only interesting if more than one parser is grouped in the same class. That is the case with this parser.
	 * @param array $spParams: Parameters of the softlink parser. Basically this is the content inside optional []-brackets after the softref keys. Parameters are exploded by ";"
	 * @param string $structurePath: If running from inside a FlexForm structure, this is the path of the tag.
	 * @return array Result array on positive matches, see description in class.t3lib_softrefproc.php. Otherwise false
	 * @see \TYPO3\CMS\Core\Database\SoftReferenceIndex
	 */
	public function findRef($table, $field, $uid, $content, $spKey, $spParams, $structurePath='') {
		if ($table !== 'tt_content') {
			return FALSE;
		}

		$this->tokenID_basePrefix = $table.':'.$uid.':'.$field.':'.$structurePath.':'.$spKey;

		$retVal = FALSE;
		$record = BackendUtility::getRecord($table, $uid);
		switch($spKey)	{
			case 'kbnescefe_layout':
				if (($record['CType'] === 'list') && ($record['list_type'] === 'kbnescefe_pi1')) {
					$retVal = $this->findRef_layout($content, $spParams);
				}
			break;
		}

		return $retVal;
	}

	/**
	 * Returning a softref configuration for a simple value field
	 * Gets used for the layout column (can be different on export and import system) and when enabled for the nescefe template record
	 *
	 * @param string The input content to analyse
	 * @param array Parameters set for the softref parser key in TCA/columns
	 * @return array Result array on positive matches, see description above. Otherwise false
	 */
	function findRef_layout($content, $spParams) {
		return $this->getSoftrefTable('tx_kbnescefe_layout', $content);
	}

	/**
	 * Generates a soft reference for the passed table/uid value
	 *
	 * @param string $table: The table of the record for which to return a soft reference
	 * @param integer $uid: The uid of the record for which to return a soft reference
	 * @return array The softreference array
	 */
	function getSoftrefTable($table, $uid) {
		$tokenID = $this->makeTokenID();
		return array(
			'content' => '{softref:'.$tokenID.'}',
			'elements' => array(
				0 => array(
					'matchString' => $uid,
					'subst' => array(
						'tokenID' => $tokenID,
						'tokenValue' => $uid,
						'recordRef' => $table . ':' . $uid,
						'type' => 'db',
					)
				)
			)
		);
	}

}

