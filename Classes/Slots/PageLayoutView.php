<?php
namespace ThinkopenAt\KbNescefe\Slots;

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
 * Slot for PageLayoutView signal "getTable_tt_content:renderedColumn"
 *
 * @author	Bernhard Kraft <kraftb@think-open.at>
 */

class PageLayoutView implements \TYPO3\CMS\Core\SingletonInterface {

	/**
	 * @var string Rendered column header
	 */
	protected $renderedHeader = '';

	/**
	 * @var string Rendered column content
	 */
	protected $renderedContent = '';

	/**
	 * This resets the column header/content value
	 *
	 * @return void
	 */
	public function reset() {
		$this->renderedHeader = '';
		$this->renderedContent = '';
	}

	/**
	 * This is the slot which will receive the "renderedColumn" signal
	 *
	 * @param string $header: The rendered column header
	 * @param string $content: The rendered column content
	 * @return void
	 */
	public function handleRenderedColumn($header, $content) {
		$this->renderedHeader = $header;
		$this->renderedContent = $content;
	}

	/**
	 * Returns the previously rendered column header
	 *
	 * @param string The rendered header
	 */
	public function getRenderedHeader() {
		return $this->renderedHeader;
		
	}

	/**
	 * Returns the previously rendered column content
	 *
	 * @param string The rendered content
	 */
	public function getRenderedContent() {
		return $this->renderedContent;
	}

}

