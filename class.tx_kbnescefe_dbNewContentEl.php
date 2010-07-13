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
 * Adds a icon for the nested content element container to the new element wizard.
 *
 * @author	Bernhard Kraft <kraftb@think-open.at>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 */


class tx_kbnescefe_dbNewContentEl {
	var $wizardItem = array();

	/**
	 * Easteregg-Method: This method determines the date of the easter sunday for the passed year.
	 * The icon of a kb_nescefe container will be different in the easter week (from 1 week before easter sunday till easter monday)
	 *
	 * Just change the date on your dev-server to try this easter egg out ;)
	 *
	 * @param integer The year for which to determine easter sunday
	 * @return integer The timestamp of easter sunday for the passed year
	 */
	function easter_sunday($year) {
		$J = date ("Y", mktime(0, 0, 0, 1, 1, $year));
		$a = $J % 19;
		$b = $J % 4;
		$c = $J % 7;
		$m = number_format (8 * number_format ($J / 100) + 13) / 25 - 2;
		$s = number_format ($J / 100 ) - number_format ($J / 400) - 2;
		$M = (15 + $s - $m) % 30;
		$N = (6 + $s) % 7;
		$d = ($M + 19 * $a) % 30;

		if ($d == 29) {
			$D = 28;
		} else if ($d == 28 and $a >= 11) {
			$D = 27;
		} else {
			$D = $d;
		}

		$e = (2 * $b + 4 * $c + 6 * $D + $N) % 7;

		$easter = mktime (0, 0, 0, 3, 21, $J) + (($D + $e + 1) * 86400);
		return $easter;
	} 

	/**
	 * Hook method for modifying the wizardItems of the "New content element wizard". Will add the kb_nescefe container to the common elements
	 *
	 * @param array The currently available elements of the "New content element wizard"
	 * @return array The modified array with the kb_nescefe container element added
	 */
	function proc($wizardItems) {
		global $BACK_PATH;
		$this->wizardItem = array(
			'icon'=>'../typo3conf/ext/kb_nescefe/nested_icon.gif',
			'title' => $GLOBALS['LANG']->sL('LLL:EXT:kb_nescefe/locallang_db.xml:tt_content.CType_pi1_wizard'),
			'description'=> $GLOBALS['LANG']->sL('LLL:EXT:kb_nescefe/locallang_db.xml:tt_content.CType_pi1_desc'),
			'tt_content_defValues.' => array(
				'CType' => 'list',
				'list_type' => 'kb_nescefe_pi1',
			),
		);
		$pObj = &$GLOBALS['SOBE'];
		$parentPosition = $_GET['parentPosition'];
		$tmpUid = intval($pObj->uid_pid);
		if ((!$parentPosition) && ($tmpUid < 0)) {
			$tmpRec = t3lib_BEfunc::getRecord('tt_content', abs($tmpUid));
			if ($tmpRec && $tmpRec['parentPosition']) {
				$parentPosition = $tmpRec['parentPosition'];
			}
		}

		if ((string)$pObj->colPos != '') {	// If a column is pre-set:
			if ($pObj->uid_pid < 0) {
				$row = array();
				$row['uid'] = abs($pObj->uid_pid);
			} else {
				$row = '';
			}
			$onClickEvent = $this->onClickInsertRecord($row, $pObj->colPos, '', $pObj->uid_pid, $pObj->sys_language, $parentPosition);
		} else {
			$onClickEvent='';
		}

			// Before versions 4.3 the "onClickEvent" was not a class variable but got directly set
			// in the parentObjects "doc" template object in member variable "JScode".
		if (t3lib_div::compat_version('4.3')) {
			$pObj->onClickEvent = $onClickEvent;
		} else {
			$pObj->doc->JScode = $pObj->doc->wrapScriptTags('
                                function goToalt_doc()  {       //
                                        '.$onClickEvent.'
                                }
			');
		}


			// Warning - Easter egg! --- begin
		$now = time();
		$month = intval(strftime('%m', $now));
		if (($month==3) || ($month==4)) {
			$year = strftime('%Y', $now);
			$easter_end = $this->easter_sunday($year)+3600*24;
			$easter_begin = $easter_end-3600*24*7;
			if (($now>=$easter_begin) && ($now<=$easter_end)) {
				$this->wizardItem['icon'] = '../typo3conf/ext/kb_nescefe/nested_icon2.gif';
			}
		}
			// Warning - Easter egg! --- end
		$newWizardItems = array();
		$added = false;
		$found_common = false;
		foreach ($wizardItems as $key => $item) {
			if ($added) {
				$newWizardItems[$key] = $item;
			} else {
				if (strpos($key, 'common_')===0) {
					$newWizardItems[$key] = $item;
					$found_common = true;
				} else {
					if ($found_common) {
						$newWizardItems['common_kb_nescefe_pi1'] = $this->wizardItem;
						$newWizardItems[$key] = $item;
						$added = true;
					} else {
						$newWizardItems[$key] = $item;
					}
				}
			}
		}
		if (!$added) {
			$newWizardItems['common_kb_nescefe_pi1'] = $this->wizardItem;
		}
		
		return $newWizardItems;
	}

	/**
	 * Create on-click event value.
	 * Modified copy from: typo3/sysext/cms/layout/db_new_content_el.php
	 *
	 * @param array The record.
	 * @param string Column position value.
	 * @param integer Move uid
	 * @param integer PID value.
	 * @param integer System language
	 * @param string The position in the parent container
	 * @return string A Javascript code for creating a new record (for "onclick" event)
	 */
	function onClickInsertRecord($row, $vv, $moveUid, $pid, $sys_lang=0, $parentPosition='') {
		global $BACK_PATH;
		$table = 'tt_content';

		$location = $BACK_PATH.'alt_doc.php?edit[tt_content]['.(is_array($row)?-$row['uid']:$pid).']=new&defVals[tt_content][colPos]='.$vv.'&defVals[tt_content][sys_language_uid]='.$sys_lang.'&defVals[tt_content][parentPosition]='.$parentPosition.'&returnUrl='.rawurlencode($GLOBALS['SOBE']->R_URI);

		return 'window.location.href=\''.$location.'\'+document.editForm.defValues.value; return false;';
	}

}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['kb_nescefe/class.tx_kbnescefe_dbNewContentEl.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['kb_nescefe/class.tx_kbnescefe_dbNewContentEl.php']);
}

?>
