<?php
namespace ThinkopenAt\KbNescefe\Hooks;

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
 * Hook for the clickmenu to remove the "paste" option when necessary.
 *
 * When a container is cut to the clipboard it must not be pasted into itself or any other
 * container inside of it as this would result in the Farnsworth parabox paradoxon.
 *
 * @author	Bernhard Kraft <kraftb@think-open.at>
 */

use \TYPO3\CMS\Core\Utility\GeneralUtility;

class ClickMenu {

	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManager
	 * @inject
	 */
	protected $objectManager;

	/**
	 * @var \ThinkopenAt\KbNescefe\Domain\Repository\ContentRepository The content element repository
	 * @inject
	 */
	protected $contentRepository = NULL;

	public function __construct() {
		$this->objectManager = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
		$this->contentRepository = $this->objectManager->get('ThinkopenAt\KbNescefe\Domain\Repository\ContentRepository');
	}

	/**
	 * Processes the passed menu items
	 * Removes the "paste" option if necessary.
	 *
	 * @param $parentObject: The parent ClickMenu instance
	 * @param array $menuItems: The click/context menu items
	 * @param string $table: The record table for which the clickmenu got generated
	 * @param integer $uid: The record uid for which the clickmenu got generated
	 * @return array The processed menu items
	 */
	public function main(\TYPO3\CMS\Backend\ClickMenu\ClickMenu $parentObject, array $menuItems, $table, $uid) {
		// If paste is for other table than "tt_content": do nothing
		if ($table !== 'tt_content') {
			return $menuItems;
		}

		// If clipboard mode is not cut: do nothing
		if ( $parentObject->clipObj->currentMode() !== 'cut' ) {
			return $menuItems;
		}
		
		// If specified record/elemen does not exist: do nothing
		$element = $this->contentRepository->findByIdentifier($uid);
		if (! $element instanceof \ThinkopenAt\KbNescefe\Domain\Model\Content ) {
			return $menuItems;
		}

		// If clipboard does not contain $element or any of it's parents: do nothing
		if (!$this->contentRepository->clipboardContainsParent($parentObject->clipObj, $element)) {
			return $menuItems;
		}

		// Clipboard contains a parent of $element. "pasteafter" is not valid for this element.
		unset($menuItems['pasteafter']);
		return $menuItems;
	}

}

