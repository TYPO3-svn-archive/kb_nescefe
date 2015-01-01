<?php
namespace ThinkopenAt\KbNescefe\Context;

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
 * Context for backend
 *
 * @author	Bernhard Kraft <kraftb@think-open.at>
 */


interface ContextInterface {

	/**
	 * Returns the current page
	 *
	 * @return integer The page which is currently shown
	 */
	public function getCurrentPage();

	/**
	 * Returns whether to ignore enable fields
	 *
	 * @return boolean TRUE
	 */
	public function getIgnoreEnableFields();

	/**
	 * Returns the language which should get shown/edited
	 *
	 * @return integer The currently set language
	 */
	public function getLanguage();

	/**
	 * Returns the "languageMode" setting
	 *
	 * @return boolean The language mode setting
	 */
	public function getLanguageMode();

	/**
	 * Sets the available content area paths/labels for the currently rendered content element
	 *
	 * @param array $paths: Currently available content area paths
	 * @return void
	 */
	public function setPaths(array $paths);

	/**
	 * Returns the available content area paths/labels for the currently rendered content element
	 *
	 * @return array Currently available content area paths/labels
	 */
	public function getPaths();

	/**
	 * Returns one element of the available content area paths/labels.
	 *
	 * @param string $key: The element position for which to retrieve a label
	 * @return string|boolean The requested content area paths/labels
	 */
	public function getPath($key);

	/**
	 * Sets the currently rendered content element
	 *
	 * @param \ThinkopenAt\KbNescefe\Domain\Model\Content|NULL $renderedElement The currently rendered content element (in PageLayoutView or FormEngine)
	 * @return void
	 */
	public function setRenderedElement($renderedElement);

	/**
	 * Retrieves the currently rendered content element
	 *
	 * @return \ThinkopenAt\KbNescefe\Domain\Model\Content The currently rendered content element (in PageLayoutView or FormEngine)
	 */
	public function getRenderedElement();

	/**
	 * Sets the element position for the currently rendered content area.
	 * The "element position" is more or less the "colPos" equivalent inside a kb_nescefe container
	 *
	 * @param string $elementPosition: The element position to be stored.
	 * @return void
	 */
	public function setElementPosition($elementPosition);

	/**
	 * Returns the last set element position.
	 *
	 * @return string The stored element position
	 */
	public function getElementPosition();

}

