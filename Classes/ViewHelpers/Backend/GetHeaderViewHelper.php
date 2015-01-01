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
 * ViewHelper which returns the rendered header for the current container
 * element for the column specified via the "index" parameter.
 *
 * == Examples ==
 *
 * <code title="Render column header for column #1">
 * <nescefe:backend.getHeader index="1" columnTitle="LLL:EXT:myExt/Resources/Private/Translation/locallang.xlf:column_1" />
 * </code>
 * <output>
 * ...
 * [HTML Code for current container column #1 in page module using optional passed label as column title]
 * ...
 * </output>
 *
 * @api
 */

use \TYPO3\CMS\Backend\Utility\BackendUtility;
use \TYPO3\CMS\Core\Utility\GeneralUtility;

class GetHeaderViewHelper extends AbstractContentAreaViewHelper {

	/**
	 * Renders a content header for the page module.
	 *
	 * @param integer $index: The index of the column header which to render.
	 * @param string $columnTitle: When set this string/local-lang-label will get used as title for the column.
	 * @return string The rendered column content header
	 * @api
	 */
	public function render($index, $columnTitle = '') {
		// @todo: Add current section prefix.
		$this->elementPosition = $index;
		$this->columnIndex = $index;
		$this->columnTitle = $columnTitle;

		$this->storeCurrentPosition();

		if ($this->getTemplateVariable('disableRealRendering')) {
			return '';
		}

		// Render header
		return $this->renderViaHook();
/*
		// Use this variant if render via hook doesn't work out
		return $this->renderViaCall();
*/
	}

	/*
	 * Renders the header for a kb_nescefe content column.
	 * Variant #1: Uses a hook which is not yet implemented in PageLayoutView. Therefore an Sys-Object (AlternateImplemenatation) is needed.
	 *
	 * @return string The rendered column header
	 */
	public function renderViaHook() {
		$slotInstance = parent::renderViaHook(TRUE);
		$header = $slotInstance->getRenderedHeader();
		// @todo: Check CSS of header column. Should resemble the main column header and not the
		// header of a content element. (todo just posted here - correct it in CSS)
		$header = str_replace('>'.$this->containerElementColPos.'<', '>'.$this->getColumnTitle().'<', $header);
		return $header;
	}

}

