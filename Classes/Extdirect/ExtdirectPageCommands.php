<?php
namespace ThinkopenAt\KbNescefe\Extdirect;

/***************************************************************
*  Copyright notice
*
*  (c) 2014-2015 Bernhard Kraft (kraftb@think-open.at)
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
 * Alternate extDirect TYPO3.Components.DragAndDrop.CommandController
 * Will simply call the old CommandController methods except if there is a local implementation.
 * Gets decorated by the old CommandController.
 *
 * This class is required for properly handling drag&drop of elements in PageLayoutView (Page module).
 *
 * @author	Bernhard Kraft <kraftb@think-open.at>
 */

use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Core\Utility\MathUtility;


class ExtdirectPageCommands {

	/**
	 * The default endpoint for ExtdirectPageCommands
	 *
	 * @var \Object
	 */
	protected $defaultEndpoint = NULL;

	/**
	 * Constructor for this alternate extdirect command controller
	 *
	 * @return void
	 */
	public function __construct() {
		$defaultClass = $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ExtDirect']['TYPO3.Components.DragAndDrop.CommandController']['originalCallbackClass'];
		$this->defaultEndpoint = \TYPO3\CMS\Core\Utility\GeneralUtility::getUserObj($defaultClass, FALSE);
	}

	/**
	 * Move content element to a position and/or column.
	 *
	 * Function is called from the Page module javascript.
	 *
	 * @param integer $sourceElement  Id attribute of content element which must be moved
	 * @param string $destinationColumn Column to move the content element to
	 * @param integer $destinationElement Id attribute of the element it was dropped on
	 * @return array
	 */
	public function moveContentElement($sourceElement, $destinationColumn, $destinationElement) {
		list($prefixColpos, $column, $prefixPage, $page, $hash, $prefixKbNescefe, $parentElement, $parentPosition) = GeneralUtility::trimExplode('-', $destinationColumn);
		if (
			$prefixColpos === 'colpos' &&
			MathUtility::canBeInterpretedAsInteger($column) &&
			$prefixPage === 'page' &&
			preg_match('/[0-9a-f]{10,30}/', $hash, $matches) &&
			MathUtility::canBeInterpretedAsInteger($page) &&
			$prefixKbNescefe === 'kb_nescefe' &&
			MathUtility::canBeInterpretedAsInteger($parentElement) &&
			MathUtility::canBeInterpretedAsInteger($parentPosition)
		) {
			$parentElement = (int)$parentElement;
			$parentPosition = (int)$parentPosition;
			\ThinkopenAt\KbNescefe\Hooks\DataHandler::$dataMapDefaults['tt_content'] = array(
				'kbnescefe_parentElement' => $parentElement,
				'kbnescefe_parentPosition' => $parentPosition,
			);
		}
		$result = $this->defaultEndpoint->moveContentElement($sourceElement, $destinationColumn, $destinationElement);
		unset(\ThinkopenAt\KbNescefe\Hooks\DataHandler::$dataMapDefaults['tt_content']);
		return $result;
	}

	/**
	 * Called when a method is called which this instance doesn't implement
	 *
	 * @param string $method: The method being called
	 * @param array $arguments: The arguments being passed to the called method
	 * @return mixed
	 */
	public function __call($method, array $arguments) {
		return call_user_func_array(array($this->defaultEndpoint, $method), $arguments);
	}

}


