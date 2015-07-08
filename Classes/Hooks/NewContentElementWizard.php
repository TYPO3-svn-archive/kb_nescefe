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
 * Adds a icon for the nested content element container to the new element wizard.
 *
 * @author	Bernhard Kraft <kraftb@think-open.at>
 */

use \TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Backend\Utility\BackendUtility;

class NewContentElementWizard implements \TYPO3\CMS\Backend\Wizard\NewContentElementWizardHookInterface {

	/**
	 * @var \TYPO3\CMS\Backend\Controller\ContentElement\NewContentElementController Parent object New Content element wizard
	 */
	protected $parentObject;

	/**
	 * @var array The wizard item for a nescefe container
	 */
	protected $wizardItem = array();


	/**
	 * Modifies WizardItems array
	 * Hook method for modifying the wizardItems of the "New content element wizard". Will add the kb_nescefe container to the common elements
	 *
	 * @param array $wizardItems Array of Wizard Items
	 * @param \TYPO3\CMS\Backend\Controller\ContentElement\NewContentElementController $parentObject Parent object New Content element wizard
	 * @return void
	 */
	public function manipulateWizardItems(&$wizardItems, &$parentObject) {
		global $BACK_PATH;

		$config_CType = &$GLOBALS['TCA']['tt_content']['columns']['CType']['config'];
		$authModeDeny_CType = $config_CType['type'] == 'select' && $config_CType['authMode'] && !$GLOBALS['BE_USER']->checkAuthMode('tt_content', 'CType', 'list', $config_CType['authMode']);

		$config_list_type = &$GLOBALS['TCA']['tt_content']['columns']['list_type']['config'];
		$authModeDeny_list_type = $config_list_type['type'] == 'select' && $config_list_type['authMode'] && !$GLOBALS['BE_USER']->checkAuthMode('tt_content', 'list_type', 'kbnescefe_pi1', $config_list_type['authMode']);

		if ($authModeDeny_CType || $authModeDeny_list_type) {
			return;
		}

		$this->init($parentObject);

		list($parentPosition, $parentElement) = $this->determineParent();
		$parentParameter = '&defVals[tt_content][kbnescefe_parentPosition]=' . $parentPosition;
		$parentParameter .= '&defVals[tt_content][kbnescefe_parentElement]=' . $parentElement;
		$this->parentObject->onClickEvent = str_replace('&returnUrl=', $parentParameter . '&returnUrl=', $this->parentObject->onClickEvent);

			// Warning - Easter egg! --- begin
		$this->handleEasterEgg();
			// Warning - Easter egg! --- end

		$wizardItems = $this->insertWizardItem($wizardItems);
	}

	/**
	 * Initializes the new element wizard
	 *
	 * @param \TYPO3\CMS\Backend\Controller\ContentElement\NewContentElementController $parentObject Parent object New Content element wizard
	 * @return void
	 */
	protected function init(\TYPO3\CMS\Backend\Controller\ContentElement\NewContentElementController $parentObject) {
		$this->parentObject = $parentObject;

		$ll = 'LLL:EXT:kb_nescefe/Resources/Private/Language/locallang_db.xlf:';

		$this->wizardItem = array(
			'icon' => '../' . ExtensionManagementUtility::siteRelPath('kb_nescefe') . 'Resources/Public/Images/nested_icon.gif',
			'title' => $GLOBALS['LANG']->sL($ll . 'tt_content.CType_pi1_wizard'),
			'description'=> $GLOBALS['LANG']->sL($ll . 'tt_content.CType_pi1_desc'),
			'tt_content_defValues' => array(
				'CType' => 'list',
				'list_type' => 'kbnescefe_pi1',
			),
		);
		$this->wizardItem['params'] = GeneralUtility::implodeArrayForUrl('defVals[tt_content]', $this->wizardItem['tt_content_defValues']);
	}

	/**
	 * Determines the parent element/position of the element to be newly created. Can get passed as "GET" variable when an
	 * appropriate link in the page module has been clicked. When no parent element/position is passed via GET the "uid_pid"
	 * value of the parent object is checked. If a "Create new content element after this one" button has been clicked
	 * uid_pid will contain the negative UID of the previous content element. If the previous content element is
	 * already in a container this one should be in the same container!
	 *
	 * @return string The parent position to be used.
	 */
	protected function determineParent() {
		$parentPosition = GeneralUtility::_GP('kbnescefe_parentPosition');
		$parentElement = GeneralUtility::_GP('kbnescefe_parentElement');
		$defVals = GeneralUtility::_GP('defVals');
		$parentPosition = !empty($parentPosition) ? $parentPosition : $defVals['tt_content']['kbnescefe_parentPosition'];
		$parentElement = !empty($parentElement) ? $parentElement : $defVals['tt_content']['kbnescefe_parentElement'];
		$tmpUid = intval($this->parentObject->uid_pid);
		if (!($parentPosition && $parentElement) && ($tmpUid < 0)) {
			$tmpRec = BackendUtility::getRecord('tt_content', abs($tmpUid));
			if ($tmpRec && strlen($tmpRec['kbnescefe_parentPosition']) && strlen($tmpRec['kbnescefe_parentElement'])) {
				$parentPosition = $tmpRec['kbnescefe_parentPosition'];
				$parentElement = $tmpRec['kbnescefe_parentElement'];
			}
		}
		return array($parentPosition, $parentElement);
	}

	/**
	 * This method inserts the kb_nescefe wizard item as the last wizard item in the "common" section.
	 * If there is no "common" section the kb_nescefe wizard item will get appended at the very end.
	 * This method could eventually get implemented more easily. Ideas?
	 *
	 * @param array $wizardItems: The wizard items as passed to the hook.
	 * @return array The wizard item array with the kb_nescefe wizard item inserted.
	 */
	protected function insertWizardItem($wizardItems) {
		$newWizardItems = array();
		$added = false;
		$found_common = false;
		foreach ($wizardItems as $key => $item) {
			if (!$added) {
				// First search for a key beginning with "common_"
				if (strpos($key, 'common_')===0) {
					// As long as there are "common_" keys just copy them into newWizardItems
					$newWizardItems[$key] = $item;
					$found_common = true;
				} else {
					if ($found_common) {
						// This is not key "common_" but "found_common" is set. So the previous key must have
						// been a "common_" key. Now it is time to insert the wizardItem for kb_nescefe.
						$newWizardItems['common_kbnescefe_pi1'] = $this->wizardItem;
						$newWizardItems[$key] = $item;
						// Remember that kb_nescefe wizard item has been added.
						$added = true;
					} else {
						// The wizardItem has not been inserted nor has any "common_" key been found.
						// Just copy over elements into newWizardItems
						$newWizardItems[$key] = $item;
					}
				}
			} else {
				// kb_nescefe wizard item has already been added. Just continue to copy over the remaining
				// wizard items.
				$newWizardItems[$key] = $item;
			}
		}
		if (!$added) {
			// If the kb_nescefe wizard item has not been added (because there are no items of type "common")
			// then append the kb_nescefe wizard item to the list of wizards.
			$newWizardItems['common_kbnescefe_pi1'] = $this->wizardItem;
		}
		return $newWizardItems;
	}

	/**
	 * Easteregg-Method: This method determines the date of the easter sunday for the passed year.
	 * The icon of a kb_nescefe container will be different in the easter week (from 1 week before easter sunday till easter monday)
	 *
	 * Just change the date on your dev-server to try this easter egg out ;)
	 *
	 * @param integer The year for which to determine easter sunday
	 * @return integer The timestamp of easter sunday for the passed year
	 */
	protected function easter_sunday($year) {
		$J = date ('Y', mktime(0, 0, 0, 1, 1, $year));
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
	 * This method overwrites the wizard icon during the easter week.
	 *
	 * @return void
	 */
	protected function handleEasterEgg() {
		$now = time();
		$month = intval(strftime('%m', $now));
		if (($month==3) || ($month==4)) {
			$year = strftime('%Y', $now);
			$easter_end = $this->easter_sunday($year)+3600*24;
			$easter_begin = $easter_end-3600*24*7;
			if (($now>=$easter_begin) && ($now<=$easter_end)) {
				$this->wizardItem['icon'] = '../' . ExtensionManagementUtility::siteRelPath('kb_nescefe') . 'Resources/Public/Images/nested_icon2.gif';
			}
		}
	}

}

