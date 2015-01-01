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
 * Alternate query settings which will get instanciated while
 * kb_nescefe code is executed in BE context.
 *
 * @author	Bernhard Kraft <kraftb@think-open.at>
 */

class QuerySettings extends \ThinkopenAt\KbNescefe\AlternateImplementations\Typo3QuerySettings {

	/**
	 * Will always return TRUE.
	 *
	 * @return bool
	 */
	public function getIgnoreEnableFields() {
		return TRUE;
	}

}

