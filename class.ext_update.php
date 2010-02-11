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
 * Extension update class
 *
 * @author	Bernhard Kraft <kraftb@think-open.at>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 */


class ext_update {

	function access() {
		return true;
	}

	function main() {
		$doUpdate = t3lib_div::_GP('button');
		$content = '';
		$content .= '<div style="width: 500px;">';
		$content .= '<h1>'.$GLOBALS['LANG']->sL('LLL:EXT:kb_nescefe/locallang.xml:update_label').'</h1>';
		if ($doUpdate) {

				// Updating content elements contained in nescefe plugins
			$upd = array(
				'parentPosition' => 'colPos',
			);
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tt_content', 'colPos LIKE \'%\_\_%\' AND colPos!=parentPosition', $upd, array('parentPosition'));
			$aff1 = $GLOBALS['TYPO3_DB']->sql_affected_rows();

			$upd = array(
				'colPos' => $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kb_nescefe']['containerElementColPos'],
			);
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tt_content', 'colPos LIKE \'%\_\_%\' AND colPos=parentPosition', $upd);
			$aff2 = $GLOBALS['TYPO3_DB']->sql_affected_rows();

				// Updating nescefe plugins
			$upd = array(
				'CType' => 'list',
				'list_type' => 'kb_nescefe_pi1',
			);
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tt_content', 'CType=\'kb_nescefe_pi1\'', $upd);
			$aff3 = $GLOBALS['TYPO3_DB']->sql_affected_rows();

				// Generating output of 
			if ($aff1 == $aff2) {
				if ($aff1 || $aff3) {
					$content .= '<p>'.sprintf($GLOBALS['LANG']->sL('LLL:EXT:kb_nescefe/locallang.xml:update_finished'), $aff1, $aff3).'</p>';
				} else {
					$content .= '<p>'.$GLOBALS['LANG']->sL('LLL:EXT:kb_nescefe/locallang.xml:update_none').'</p>';
				}
			} else {
				$content .= '<p>'.$GLOBALS['LANG']->sL('LLL:EXT:kb_nescefe/locallang.xml:update_error').' ('.$aff1.'/'.$aff2.'/'.$aff3.')'.'</p>';
			}
		} else {
			$content .= '<p>'.$GLOBALS['LANG']->sL('LLL:EXT:kb_nescefe/locallang.xml:update_message').'</p>';
			$content .= '<br /><br />';
			$content .= '<form action="'.t3lib_div::linkThisScript(array('update' => 'update')).'" method="POST" enctype="multipart/form-data">';
			$content .= '<input type="submit" name="button" value="Update !" />';
			$content .= '</form>';
		}
		return $content;
	}

}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['kb_nescefe/class.ext_update.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['kb_nescefe/class.ext_update.php']);
}

?>
