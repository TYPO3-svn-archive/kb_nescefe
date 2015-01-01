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
 * Item processing class
 * This class is just a wrapper for \thinkopenAt\KbNescefe\Controller\TcaItemsController which
 * uses extbase. This class ist just a wrapper for getting called by the core.
 *
 * @author	Bernhard Kraft <kraftb@think-open.at>
 */

use \TYPO3\CMS\Core\Utility\GeneralUtility;

class TcaItemsProcessing {

	/**
	 * This method alters the available items/option in the drop down for selecting the position of a tt_content element in its kb_nescefe parent.
	 *
	 * @param array $params: The variables passed to the hook
	 * @param \TYPO3\CMS\Backend\Form\FormEngine $parentObject: The object from which this user function is called
	 * @return array The processed items
	 */
	public function contentPositions($params, \TYPO3\CMS\Backend\Form\FormEngine $parentObject) {
		$objectManager = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
		$tcaItemsController = $objectManager->get('ThinkopenAt\KbNescefe\Controller\TcaItemsController');
		$tcaItemsController->contentPositions($params, $parentObject);
	}

	/**
	 * This method add the kb_nescefe column to the available list of columns so it doesn't show up as
	 * "invalid" in FormEngine fields.
	 *
	 * @param array $params: The variables passed to the hook
	 * @param \TYPO3\CMS\Backend\Form\FormEngine $parentObject: The object from which this user function is called
	 * @return array The processed items
	 */
	public function colPosHandling(array $params, \TYPO3\CMS\Backend\Form\FormEngine $parentObject) {
		GeneralUtility::callUserFunction($GLOBALS['TCA']['tt_content']['columns']['colPos']['config']['origItemsProcFunc'], $params, $parentObject);
		$params['items']['kb_nescefe'] = Array($GLOBALS['LANG']->sL('LLL:EXT:kb_nescefe/Resources/Private/Language/locallang_db.xlf:tt_content.containerColumn'), $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kb_nescefe']['containerElementColPos']);
	}


}

