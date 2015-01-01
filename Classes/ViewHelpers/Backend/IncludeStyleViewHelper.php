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
 * ViewHelper that includes the specified css file into the currently rendered
 * backend view. This is used to include styles into the PageLayoutView
 * (Page module).
 *
 * == Examples ==
 *
 * <code title="Include CSS file from extension">
 * <nescefe:includeStyle file="EXT:my_ext/Resources/Public/Css/customStyle.css" />
 * </code>
 * <output>
 * <head>
 * ...
 * <style type="text/css">
 * ... Contents of customStyle.css ...
 * </style>
 * ...
 * </head>
 * </output>
 *
 * @api
 */

use \TYPO3\CMS\Core\Utility\GeneralUtility;

class IncludeStyleViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * Includes the specified CSS file in the BE module.
	 *
	 * @param string $file: The file which should get included as CSS
	 * @return string Always returns an empty string ""
	 * @api
	 */
	public function render($file) {
		if ($this->templateVariableContainer->exists('disableRealRendering') && $this->templateVariableContainer->get('disableRealRendering')) {
			return '';
		}
		$absFile = GeneralUtility::getFileAbsFileName(trim($file));
		if (is_file($absFile) && is_readable($absFile)) {
			$GLOBALS['SOBE']->doc->inDocStylesArray['kb_nescefe_'.md5($absFile)] = GeneralUtility::getURL($absFile);
		}
		return '';
	}

}

