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
 * Can supply some information for the currently rendered page by retrieving it from
 * either a previously set "pageLayoutView" (pageModule) or "FormEngine" (edit form)
 * instance. Thus the class abstracts access to various properties required when working
 * with kb_nescefe/tt_content elements. Like for example uid of the PAGE on which is
 * shown currently or on which the currently edited element resides.
 *
 * @author	Bernhard Kraft <kraftb@think-open.at>
 */


class Backend implements \TYPO3\CMS\Core\SingletonInterface, \ThinkopenAt\KbNescefe\Context\ContextInterface {

	/**
	 * @var \TYPO3\CMS\Backend\View\PageLayoutView
	 */
	protected $pageLayoutView = NULL;

	/**
	 * @var \TYPO3\CMS\Backend\Form\DataPreprocessor
	 */
	protected $dataPreprocessor = NULL;

	/**
	 * @var array<string:string|boolean> An array with content element positions as keys and their label (or TRUE) as value
	 */
	protected $paths = array();

	/**
	 * @var array<string:string> An array with section positions as keys and section labels as value
	 */
	protected $sectionLabels = array();

	/**
	 * @var \ThinkopenAt\KbNescefe\Domain\Model\Content The content element being rendered currently in PageLayoutView or FormHandler
	 */
	protected $renderedElement = NULL;

	/**
	 * @var integer|NULL An explicitely language which should get used
	 */
	protected $language = NULL;

	/**
	 * @var integer|NULL The page which is currently shown (on which content elements get edited)
	 */
	protected $currentPage = NULL;

	/**
	 * Sets a PageLayoutView object instance
	 *
	 * @param \TYPO3\CMS\Backend\View\PageLayoutView $pageLayoutView: The page layout object (TYPO3 core class)
	 * @return void
	 */
	public function setPageLayoutView(\TYPO3\CMS\Backend\View\PageLayoutView $pageLayoutView) {
		$this->pageLayoutView = $pageLayoutView;
	}

	/**
	 * Sets a FormHandler object instance
	 *
	 * @param \TYPO3\CMS\Backend\Form\DataPreprocessor $parentObject: The BE data preprocessor object instance
	 * @return void
	 */
	public function setDataPreprocessor(\TYPO3\CMS\Backend\Form\DataPreprocessor $dataPreprocessor) {
		$this->dataPreprocessor = $dataPreprocessor;
	}

	/**
	 * Returns the PageLayoutView object instance
	 *
	 * @return \TYPO3\CMS\Backend\View\PageLayoutView The page layout object (TYPO3 core class)
	 */
	public function getPageLayoutView() {
		return $this->pageLayoutView;
	}

	/**
	 * Explicitely sets the current page
	 *
	 * @param integer The page which should get used as current page
	 * @return void
	 */
	public function setCurrentPage($currentPage) {
		$this->currentPage = $currentPage;
	}

	/**
	 * Returns the current page
	 *
	 * @return integer The page which is currently shown. Or the page on which the currently edited document resides.
	 */
	public function getCurrentPage() {
		if ($this->currentPage !== NULL) {
			return $this->currentPage;
		} elseif ($this->pageLayoutView) {
			return $this->pageLayoutView->id;
		} elseif ($this->renderedElement) {
			return $this->renderedElement->getPid();
		}
		return 0;
	}

	/**
	 * Returns the page from which content is shown.
	 * For the backend this is simply the same as "getCurrentPage"
	 *
	 * @return integer The from which content is shown.
	 */
	public function getContentPage() {
		return $this->getCurrentPage();
	}

	/**
	 * Always returns TRUE as enable fields should always get ignored in backend context
	 *
	 * @return boolean TRUE
	 */
	public function getIgnoreEnableFields() {
		return TRUE;
	}

	/**
	 * Returns the "option_newWizard" setting
	 *
	 * @return boolean Whether a "new content element" button should show a wizard instead of the new content element list.
	 */
	public function getOptionNewWizard() {
		return $this->pageLayoutView->option_newWizard;
	}

	/**
	 * Returns the "doEdit" setting
	 *
	 * @return boolean Whether editing is enabled.
	 */
	public function getDoEdit() {
		return $this->pageLayoutView->doEdit;
	}

	/**
	 * Returns the language which is currently explicitely set or the one set in the page module
	 *
	 * @return integer The currently set language in the parent object
	 */
	public function getLanguage() {
		if ($this->language !== NULL) {
			return $this->language;
		} else {
			return $this->pageLayoutView->tt_contentConfig['sys_language_uid'];
		}
	}

	/**
	 * Explicitely sets a language
	 *
	 * @param integer $language: The language which should get set
	 * @return void
	 */
	public function setLanguage($language) {
		$this->language = $language;
	}

	/**
	 * Returns the language parameter as it is required by the page module
	 *
	 * @return integer The language uid which should get used. Depends on "defLangBinding" and currently set language.
	 */
	public function getLanguageParam() {
		if ($this->pageLayoutView->defLangBinding && ($this->pageLayoutView->tt_contentConfig['sys_language_uid'] == 0)) {
			$lP = 0;
		} else {
			$lP = $pageLayoutView->tt_contentConfig['sys_language_uid'];
		}
		return $lP;
	}

	/**
	 * Returns the "showInfo" flag
	 *
	 * @return boolean The show info flag
	 */
	public function getShowInfo() {
		return $this->pageLayoutView->tt_contentConfig['showInfo'];
	}

	/**
	 * Returns the "languageMode" flag
	 *
	 * @return boolean The language mode flag
	 */
	public function getLanguageMode() {
		return $this->pageLayoutView->tt_contentConfig['languageMode'];
	}

	/**
	 * Returns the def lang bindign setting
	 *
	 * @return boolean Returns the defLangBinding setting of the page module
	 */
	public function getDefLangBinding() {
		return $this->pageLayoutView->defLangBinding;
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
	 * Sets the section labels for the currently rendered content element
	 *
	 * @param array $sectionLabels: Section labels for the currently rendered content element
	 * @return void
	 */
	public function setSectionLabels(array $sectionLabels) {
		$this->sectionLabels = $sectionLabels;
	}

	/**
	 * Returns the section labels for the currently rendered content element
	 *
	 * @return array Section labels for the currently rendered content element
	 */
	public function getSectionLabels() {
		return $this->sectionLabels;
	}

	/**
	 * Returns the section label of the requested content element position for the currently rendered content element
	 *
	 * @param string $key: The element position for which to retrieve a section label
	 * @return string|NULL Section labels for the requested content area of the currently rendered content element
	 */
	public function getSectionLabel($key) {
		return isset($this->sectionLabels[$key]) ? $this->sectionLabels[$key] : NULL;
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

