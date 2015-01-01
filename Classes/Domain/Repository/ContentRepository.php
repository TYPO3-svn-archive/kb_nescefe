<?php
namespace ThinkopenAt\KbNescefe\Domain\Repository;

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
 * Repository for content elements
 *
 * @author	Bernhard Kraft <kraftb@think-open.at>
 */


use \ThinkopenAt\KbNescefe\Domain\Model\Content;

class ContentRepository extends \TYPO3\CMS\Extbase\Persistence\Repository {

	/**
	 * @var \ThinkopenAt\KbNescefe\Context\ContextInterface The context which to use for retrieving content elements
	 */
	protected $context;

	public function setContext(\ThinkopenAt\KbNescefe\Context\ContextInterface $context) {
		$this->context = $context;
	}

	/**
	 * Retrieves a content element by specifying its identifier (UID)
	 *
	 * @param integer The identifier (uid) of the content element which to retrieve
	 * @param boolean $returnRawQueryResult: avoids the object mapping by the persistence layer
	 * @return ThinkopenAt\KbNescefe\Domain\Model\Content|NULL The requested content element object
	 */
	public function findByIdentifier($identifier, $returnRawQueryResult = FALSE) {
		$query = $this->createQuery();
		$querySettings = $query->getQuerySettings();

		if ($this->context) {
			$page = $this->context->getCurrentPage();
			$querySettings->setRespectStoragePage(TRUE)->setStoragePageIds(array($page));
		} else {
			$querySettings->setRespectStoragePage(FALSE);
		}
		$querySettings->setRespectSysLanguage(FALSE);

//		$querySettings->setEnableLanguageOverlay(FALSE);

		$result = $query->matching($query->equals('uid', $identifier))->execute($returnRawQueryResult);
		if ($returnRawQueryResult) {
			return $result;
		} else {
			return $result->getFirst();
		}
	}

	/**
	 * Retrieves all content elements which are contained in the passed content element.
	 * So this method finds all content elements by specifying the parent.
	 *
	 * @param \ThinkopenAt\KbNescefe\Domain\Model\Content $contentElement: The content element for which to retrieve all contained elements
	 * @param string $positionInParent: If specified only those content elements get returned which are at the specified position.
	 * @param boolean $returnRawQueryResult: avoids the object mapping by the persistence layer
	 * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface<\ThinkopenAt\KbNescefe\Domain\Model\Content> All contained elements
	 */
	public function findByParent(Content $contentElement, $positionInParent = '', $returnRawQueryResult = FALSE) {
		$query = $this->createQuery();

		// @TODO: Set querySettings to appropriately handle hidden/language
		/*
		$showHidden = $this->context->showHidden();
		$showLanguage = $this->context->showLanguage();
		$showLanguage = $this->pObj->defLangBinding && $this->lP == 0 ? ' AND sys_language_uid IN (0,-1)' : ' AND sys_language_uid=' . $this->lP;
		// --> TODO: Set "respectStoragePage" to TRUE and set "pageID"
		$queryParts = $this->pObj->makeQueryArray('tt_content', $this->pageID, $cpospart . $showHidden . $showLanguage);
		*/

//		$query->getQuerySettings()->setRespectStoragePage(FALSE);
		$querySettings = $query->getQuerySettings();

		$page = $this->context->getCurrentPage();
		$querySettings->setRespectStoragePage(TRUE)->setStoragePageIds(array($page));
		$querySettings->setRespectSysLanguage(FALSE);

//		$querySettings->setEnableLanguageOverlay(FALSE);

		$matchParent = $query->equals('parentElement', $contentElement->getUid());
		$querySettings = $query->getQuerySettings();

//		So oder so?
//		if ($querySettings->getRespectSysLanguage() && $querySettings->getLanguageUid() && ($l18n_parent = $contentElement->getL18nParent()) > 0) {
/*
		if (($l18n_parent = $contentElement->getL18nParent()) > 0) {
			$matchLanguageParent = $query->equals('parentElement', $l18n_parent);
			$matchParent = $query->logicalOr($matchParent, $matchLanguageParent);
		}
*/

		if (strlen($positionInParent)) {
			$matchPosition = $query->equals('parentPosition', $positionInParent);
			$matchParent = $query->logicalAnd($matchParent, $matchPosition);
		}

		$query->matching($matchParent);

		return $query->execute($returnRawQueryResult);
//		$this->func->getSectionMax($storage);
//		return $storage;
	}

	/**
	 * Check wheter the passed clipboard contains any record which exactly matches this element or 
	 * any of the records contained in the passed clipboard is a parent of the this element.
	 * element.
	 *
	 * @param \TYPO3\CMS\Backend\Clipboard\Clipboard $clipboard: The clipboard which to check
	 * @param \ThinkopenAt\KbNescefe\Domain\Model\Content $element: The element for which to check wheter it (or any of its parents) is on the clipboard
	 * @return boolean Returns TRUE if the clipboard contains any element being a parent of the passed element
	 */
	public function clipboardContainsParent(\TYPO3\CMS\Backend\Clipboard\Clipboard $clipboard, Content $element) {
		$elFromTable = $clipboard->elFromTable('tt_content');
		if (count($elFromTable)) {
			foreach ($elFromTable as $clipboardItem => $set) {
				list($table, $uid) = explode('|', $clipboardItem);
				if ($table !== 'tt_content') {
					continue;
				}
				$clipboardElement = $this->findByIdentifier($uid);
				if (! $clipboardElement instanceof \ThinkopenAt\KbNescefe\Domain\Model\Content ) {
					continue;
				}

				/*
				// Don't allow to paste an element into itself.
				// Will also get checked by below call so there is no need to check that here
				if ($element->getUid() === $clipboardElement->getUid()) {
					return TRUE;
				}
				*/

				// Don't allow to paste any parent into the currently rendered element
				if ($this->elementHasParent($element, $clipboardElement)) {
					return TRUE;
				}
			}
		}
		return FALSE;
	}

	/**
	 * Checks wheter the passed element has the passed element $parent as its (grand-)parents.
	 *
	 * @param \ThinkopenAt\KbNescefe\Domain\Model\Content $element: The element whose parents to check
	 * @param \ThinkopenAt\KbNescefe\Domain\Model\Content $parent: Check whether this is a valid parent of $element
	 * @return boolean Returns TRUE if element has $parent as parent (or grandparent, grand-grand-parent, ...)
	 */
	public function elementHasParent(Content $element, Content $parent) {
		$parentUid = $parent->getUid();
		while ($element instanceof Content) {
			if ($parentUid === $element->getUid()) {
				return TRUE;
			}
			$element = $element->getParentElement();
		}
		return FALSE;
	}

}

