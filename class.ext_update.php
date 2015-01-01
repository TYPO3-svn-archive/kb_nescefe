<?php
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
 * Extension update class
 *
 * @author	Bernhard Kraft <kraftb@think-open.at>
 */

class ext_update {

	function access() {
		return true;
	}

	function main() {
		$doUpdate = t3lib_div::_GP('button');
		$content = '';
		$content .= '<div style="width: 500px;">';
		$content .= '<h1>'.$GLOBALS['LANG']->sL('LLL:EXT:kb_nescefe/Resources/Private/Language/locallang.xlf:update_label').'</h1>';
		if ($doUpdate) {

			$affected = array();

				// Updating content elements contained in nescefe plugins
			$upd = array(
				'parentPosition' => 'colPos',
			);
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tt_content', 'colPos LIKE \'%\_\_%\' AND colPos!=parentPosition', $upd, array('parentPosition'));
			$affected[] = $GLOBALS['TYPO3_DB']->sql_affected_rows();

			$upd = array(
				'colPos' => $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kb_nescefe']['containerElementColPos'],
			);
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tt_content', 'colPos LIKE \'%\_\_%\' AND colPos=parentPosition', $upd);
			$affected[] = $GLOBALS['TYPO3_DB']->sql_affected_rows();

				// Updating nescefe plugins
			$upd = array(
				'CType' => 'list',
				'list_type' => 'kb_nescefe_pi1',
			);
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tt_content', 'CType=\'kb_nescefe_pi1\'', $upd);
			$affected[] = $GLOBALS['TYPO3_DB']->sql_affected_rows();

			// ----------------- Version 2.0 Updates -----------------------

			// 1. Updating nescefe plugins
			// Before the plugin was named "kb_nescefe_pi1" now it is named "kbnescefe_pi1"
			$upd = array(
				'list_type' => 'kbnescefe_pi1',
			);
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tt_content', 'CType=\'list\' AND list_type=\'kb_nescefe_pi1\'', $upd);
			$affected[] = $GLOBALS['TYPO3_DB']->sql_affected_rows();

			// 2. Split up "parentPosition" into "parentElement" and "parentPosition"
			$upd = array(
				'kbnescefe_parentElement' => 'SUBSTRING(parentPosition FROM 1 FOR LOCATE(\'__\', parentPosition)-1)',
				'kbnescefe_parentPosition' => 'SUBSTRING(parentPosition FROM LOCATE(\'__\', parentPosition)+2)',
			);
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tt_content', 'parentPosition LIKE \'%\_\_%\' AND kbnescefe_parentElement=0 AND kbnescefe_parentPosition=\'\'', $upd, array('kbnescefe_parentPosition', 'kbnescefe_parentElement'));
			$affected[] = $GLOBALS['TYPO3_DB']->sql_affected_rows();

			// Rename tx_kbnescefe_containers table into tx_kbnescefe_layout (if no records already in tx_kbnescefe_layout and tx_kbnescefe_containers exists)
			$row3 = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow('table_name', 'information_schema.tables', 'table_schema=\''.$GLOBALS['TYPO3_CONF_VARS']['DB']['database'].'\' AND table_name=\'tx_kbnescefe_containers\'');
			if ($row3['table_name'] === 'tx_kbnescefe_containers') {
				$row1 = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow('count(*) AS cnt', 'tx_kbnescefe_layout', '1=1');
				$row2 = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow('count(*) AS cnt', 'tx_kbnescefe_containers', '1=1');
				if ($row1['cnt'] && $row2['cnt']) {
					$content .= '<p>'.$GLOBALS['LANG']->sL('LLL:EXT:kb_nescefe/Resources/Private/Language/locallang.xlf:update_layout_table_exists').'</p>';
				} elseif ($row2['cnt']) {
					$GLOBALS['TYPO3_DB']->sql_query('DROP TABLE IF EXISTS tx_kbnescefe_layout');
					$GLOBALS['TYPO3_DB']->sql_query('RENAME TABLE tx_kbnescefe_containers TO tx_kbnescefe_layout');
				}
			}

			// Generate output
			$content .= '<p>'.sprintf($GLOBALS['LANG']->sL('LLL:EXT:kb_nescefe/Resources/Private/Language/locallang.xlf:update_finished'), implode(', ', $affected)).'</p>';
		} else {
			$content .= '<p>'.$GLOBALS['LANG']->sL('LLL:EXT:kb_nescefe/Resources/Private/Language/locallang.xlf:update_message').'</p>';
			$content .= '<br /><br />';
			$content .= '<form action="'.t3lib_div::linkThisScript(array('update' => 'update')).'" method="POST" enctype="multipart/form-data">';
			$content .= '<input type="submit" name="button" value="Update !" />';
			$content .= '</form>';
		}
		return $content;
	}

}

