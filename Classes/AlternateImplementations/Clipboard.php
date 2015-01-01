<?php
namespace ThinkopenAt\KbNescefe\AlternateImplementations;

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
 * Extends "Clipboard"
 *
 * @author	Bernhard Kraft <kraftb@think-open.at>
 */

use \TYPO3\CMS\Core\Utility\GeneralUtility;

class Clipboard extends \TYPO3\CMS\Backend\Clipboard\Clipboard {

	/**
	 * Add parentElement/parentPosition parameters to the $update parameter
	 *
	 * @param string $table Tablename (_FILE for files)
	 * @param mixed $uid "destination": can be positive or negative indicating how the paste is done (paste into / paste after)
	 * @param boolean $setRedirect If set, then the redirect URL will point back to the current script, but with CB reset.
	 * @param array|NULL $update Additional key/value pairs which should get set in the moved/copied record (via DataHandler)
	 * @return string
	 * @todo Define visibility
	 */
	public function pasteUrl($table, $uid, $setRedirect = TRUE, array $update = NULL) {
		if ($table === 'tt_content') {
			$objectManager = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
			$context = $objectManager->get('ThinkopenAt\KbNescefe\Context\Backend');
			if (is_object($parentElement = $context->getRenderedElement())) {
				$update['kbnescefe_parentPosition'] = $context->getElementPosition();
				$update['kbnescefe_parentElement'] = $parentElement->getUid();
			}
		}
		return parent::pasteUrl($table, $uid, $setRedirect, $update);
	}

}

