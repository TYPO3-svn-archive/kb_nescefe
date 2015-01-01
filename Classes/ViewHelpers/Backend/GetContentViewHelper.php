<?php
namespace ThinkopenAt\KbNescefe\ViewHelpers\Backend;

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
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * ViewHelper which returns the rendered content elements for the current container
 * element for the column specified via the "index" parameter.
 *
 * == Examples ==
 *
 * <code title="Render content elements for column #1">
 * <nescefe:backend.getContent index="1" />
 * </code>
 * <output>
 * ...
 * [HTML Code for content elment in column #1 of the currently rendered container]
 * ...
 * </output>
 *
 * @api
 */

use \TYPO3\CMS\Core\Utility\GeneralUtility;

class GetContentViewHelper extends AbstractContentAreaViewHelper {


	/**
	 * Renders a content header for the page module.
	 *
	 * @param integer $index: The index of the column header which to render.
	 * @param string $columnTitle: When set this string/local-lang-label will get used as title for the column.
	 * @return string The rendered column content header
	 * @api
	 */
	public function render($index) {
		// @todo: Add current section prefix.
		$this->elementPosition = $index;
		$this->columnIndex = $index;

		$this->storeCurrentPosition();

		if ($this->getTemplateVariable('disableRealRendering')) {
			return '';
		}

		return $this->renderViaHook();
/*
		// Use this variant if render via hook doesn't work out
		return $this->renderViaCall();
*/
	}

	/*
	 * Renders the content elements inside a kb_nescefe container column / content area
	 * Variant #1: Uses a hook which is not yet implemented in PageLayoutView. Therefore an Sys-Object (AlternateImplemenatation) is needed.
	 *
	 * @return string The rendered content elements
	 */
	public function renderViaHook() {
		$slotInstance = parent::renderViaHook();
		$content = $slotInstance->getRenderedContent();
		$content = preg_replace('/id="(colpos-' . $this->containerElementColPos . '-page-[1-9][0-9]*-[0-9a-f]+)"/', 'id="$1-kb_nescefe-' . $this->context->getRenderedElement()->getUid() . '-' . $this->elementPosition . '"', $content, 1);
		return $content;
	}

	/*
	 * Renders the content elements inside a kb_nescefe container column / content area
	 *
	 * Variant #2: Directly calls the appropriate methods in PageLayoutView.
	 * To achive this a similar environment/context has to get created as when rendering content elements in PageLayoutView.
	 * This can prove to get quite complicated and is more error prone to future updates of PageLayoutView than adding
	 * a few simple hooks in an extended version of PageLayoutView.
	 *
	 * @return string The rendered content elements
	 */
	public function renderViaCall() {
/*
		// @todo: Reimplement the page rendering of PageLayoutView
		// Below code are remnants of previous versions of kb_nescefe and wont work with current TYPO3 versions.

		$pageLayoutView->generateTtContentDataArray($currentElements);
		$elementIndex = 0;
		foreach ($currentElements as $currentElement) {
			$this->renderElement($currentElement);

			$pageLayoutView->tt_contentData['prev'] = array();
			$pageLayoutView->tt_contentData['next'] = array();
			$pageLayoutView->tt_contentData['nextThree'] = array();

			$rowContent = '';
			$row = $currentElement->getRaw();
			$elementUid = $currentElement->getUid();
			$pageUid = $currentElement->getPid();
			$lP = $currentElement->getSysLanguageUid();

			// Set "prev" value for moving content element $elementUid up
			// The first element on a page can't get moved up for all others:
			if ($elementIndex) {
				if ($elementIndex > 1) {
					// The third and any later element gets moved up by moving it AFTER the second-previous element
					$pageLayoutView->tt_contentData['prev'][$elementUid] = -$currentElements[$keys[$elementIndex-2]]->getUid();
				} else {
					// The second element on a page/in a column gets moved up by moving it into the position of the first element of the page
					$pageLayoutView->tt_contentData['prev'][$elementUid] = $pageUid;
				}
			}

			// Set "next" value for moving $elementUid down
			$nextElement = $currentElements[$keys[$elementIndex+1]];
			if ($nextElement) {
				$pageLayoutView->tt_contentData['next'][$elementUid] = -$nextElement->getUid();
			}

			// Set the "nextThree" elements
			for ($x = 0; $x < $pageLayoutView->nextThree; $x++)	{
				$nextElement = $currentElements[$keys[$elementIndex+$x]];
				if ($nextElement) {
					$pageLayoutView->tt_contentData['nextThree'][$elementUid] .= $nextElement->getUid().',';
				}
				$pageLayoutView->tt_contentData['nextThree'][$elementUid] = rtrim($pageLayoutView->tt_contentData['nextThree'][$elementUid], ',');
			}

			$isRTE = $GLOBALS['BE_USER']->isRTE() && $pageLayoutView->isRTEforField('tt_content', $row, 'bodytext');
			$rowContent .= $pageLayoutView->tt_content_drawHeader(
				$row,
				$this->context->getShowInfo() ? 15 : 5,
				$this->context->getDefLangBinding() && $lP>0,
				TRUE,
				!$this->context->getLanguageMode()
			);

			$rowContent .= '<div '.($row['_ORIG_uid'] ? ' class="ver-element"' :'').'>';
			$rowContent .= $pageLayoutView->tt_content_drawItem($row, $isRTE, $lP);
			$rowContent .= '</div>';

			$rowContent .= '</div>';

			$statusHidden = ($this->pObj->isDisabled('tt_content', $row) ? ' t3-page-ce-hidden' : '');
			$row_code = '<div class="t3-page-ce' . $statusHidden . '">' . $row_code. '</div>';

			$code .= $row_code;
			$elementIndex++;
		}
		return $content;
*/
	}

	/**
	 * This method renders a single content element.
	 * The code is taken from PageLayoutView method "getTable_tt_content()".
	 *
	 */
/*
	protected function renderElement($currentElement) {
	}
*/

}

