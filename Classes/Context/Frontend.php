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
 * Context for frontend
 * Allows to retrieve value/settings for frontend rendering.
 *
 * @author Bernhard Kraft <kraftb@think-open.at>
 */


class Frontend implements \TYPO3\CMS\Core\SingletonInterface, \ThinkopenAt\KbNescefe\Context\ContextInterface {

	/**
	 * Page on which a container is located if the container is not on the current content page
	 * This is will be set for example when an "insertRecords" element is used to render a kb_nescefe
	 * container.
	 *
	 * @var integer
	 */
	protected $containerPage = 0;

	/**
	 * Returns the current page
	 *
	 * @return integer The page which is currently shown
	 */
	public function getCurrentPage() {
		return $GLOBALS['TSFE']->id;
	}

	/**
	 * Returns the page from which to show content. This can be different from the
	 * current page for two reasons:
	 * 1. An "insertRecords" element is rendered which points to a kb_nescefe container.
	 *    In such a case the PID of the kb_nescefe container can be different from the
	 *    currently rendered page. The PID of the container will be set in containerPage.
	 * 2. The "show content from this page" feature of a page record is used. In such
	 *    a case the "contentPid" property of TSFE will point to the page from which to
	 *    retrieve content.
	 *
	 * @return integer The page which is currently shown
	 */
	public function getContentPage() {
		if ($this->containerPage) {
			return $this->containerPage;
		} elseif ($GLOBALS['TSFE']->contentPid) {
			return $GLOBALS['TSFE']->contentPid;
		} else {
			return $this->getCurrentPage();
		}
	}

	/**
	 * Sets the container page being used for retrieving the container and its content elements.
	 *
	 * @param integer $containerPage: The page on which the container is located
	 * @return void
	 */
	public function setContainerPage($containerPage) {
		$this->containerPage = $containerPage;
	}

	/**
	 * Returns whether to ignore enable fields
	 *
	 * @return boolean TRUE
	 */
	public function getIgnoreEnableFields() {
		return $GLOBALS['TSFE']->showHiddenRecords ? TRUE : FALSE;
	}

	/**
	 * Returns the language which should get shown/edited
	 *
	 * @return integer The currently set language
	 */
	public function getLanguage() {
		return $GLOBALS['TSFE']->sys_language_content;
	}

	/**
	 * Returns the "languageMode" setting
	 *
	 * @return boolean The language mode setting
	 */
	public function getLanguageMode() {
		return $GLOBALS['TSFE']->sys_language_mode;
	}

	/**
	 * Sets the available content area paths/labels for the currently rendered content element
	 *
	 * @param array $paths: Currently available content area paths
	 * @return void
	 */
	public function setPaths(array $paths) {
		$this->paths = $paths;
	}

	/**
	 * Returns the available content area paths/labels for the currently rendered content element
	 *
	 * @return array Currently available content area paths/labels
	 */
	public function getPaths() {
		return $this->paths;
	}

	/**
	 * Returns one element of the available content area paths/labels.
	 *
	 * @param string $key: The element position for which to retrieve a label
	 * @return string|boolean The requested content area paths/labels
	 */
	public function getPath($key) {
		return isset($this->paths[$key]) ? $this->paths[$key] : FALSE;
	}

	/**
	 * Sets the currently rendered content element
	 *
	 * @param \ThinkopenAt\KbNescefe\Domain\Model\Content|NULL $renderedElement The currently rendered content element (in PageLayoutView or FormEngine)
	 * @return void
	 */
	public function setRenderedElement($renderedElement) {
		$this->renderedElement = $renderedElement;
	}

	/**
	 * Retrieves the currently rendered content element
	 *
	 * @return \ThinkopenAt\KbNescefe\Domain\Model\Content The currently rendered content element (in PageLayoutView or FormEngine)
	 */
	public function getRenderedElement() {
		return $this->renderedElement;
	}

	/**
	 * Sets the element position for the currently rendered content area.
	 * The "element position" is more or less the "colPos" equivalent inside a kb_nescefe container
	 *
	 * @param string $elementPosition: The element position to be stored.
	 * @return void
	 */
	public function setElementPosition($elementPosition) {
		$this->elementPosition = $elementPosition;
	}

	/**
	 * Returns the last set element position.
	 *
	 * @return string The stored element position
	 */
	public function getElementPosition() {
		return $this->elementPosition;
	}

}

