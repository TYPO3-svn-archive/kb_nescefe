<?php
namespace ThinkopenAt\KbNescefe\ViewHelpers;

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
 * ViewHelper that retrieves the name set for a column.
 * Usually those names are just used in the backend but they can also get
 * inserted in the frontend using this view helper.
 *
 * == Examples ==
 *
 * <code title="Retrieve column name">
 * <h1><nescefe:getHeader index="1" /></h1>
 * </code>
 * <output>
 * <h1>Left content</h1>
 * </output>
 *
 * @api
 */
class GetHeaderViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * Sets a template variable to contain an iterable list of pages. Does not return anything directly.
	 *
	 * @param integer $index: The index of the column whose name to render.
	 * @return string The name of the specified column
	 * @api
	 */
	public function render($index) {
		// @todo
		return '';
	}

}


