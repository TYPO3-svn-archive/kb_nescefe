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
 * Abstract class for ViewHelpers which render a content area using
 * the existing PageModule (PageLayoutView) code.
 * 
 * @author	Bernhard Kraft <kraftb@think-open.at>
 */

use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Backend\Utility\BackendUtility;

abstract class AbstractContentAreaViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * @var \ThinkopenAt\KbNescefe\Domain\Repository\ContentRepository
	 * @inject
	 */
	protected $contentRepository;

	/**
	 * @var \ThinkopenAt\KbNescefe\Context\Backend
	 * @inject
	 */
	protected $context;

	/**
	 * @var string The position of the area in the template (as path string)
	 */
	protected $elementPosition = '';

	/**
	 * @var integer The index of the currently rendered column (0..)
	 */
	protected $columnIndex = 0;

	/**
	 * @var string The title which to use for the current column
	 */
	protected $columnTitle = '';

	/**
	 * @var integer The uid which should get set for an element in a container (configured via ext_conf_template)
	 */
	protected $containerElementColPos = 0;

	/*
	 * Constructor for a content area renderer
	 *
	 * @param string $elementPosition: The position of the current content area in its container
	 * @return void
	 */
	public function __construct() {
		$this->containerElementColPos = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['kb_nescefe']['containerElementColPos'];
	}

	/**
	 * Returns the content elements for this area
	 *
	 * @param boolean $returnRawQueryResult: avoids the object mapping by the persistence layer
	 * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface<\ThinkopenAt\KbNescefe\Domain\Model\Content> All content elements in this area
	 */
	protected function getContentElements($returnRawQueryResult = FALSE) {
		return $this->contentRepository->findByParent($this->context->getRenderedElement(), $this->elementPosition, $returnRawQueryResult);
	}

	/**
	 * Assign a value to the variable container.
	 *
	 * @param string $key The key of a view variable to set
	 * @param mixed $value The value of the view variable
	 * @return \TYPO3\CMS\Fluid\View\AbstractTemplateView the instance of this view to allow chaining
	 * @api
	 */
	public function assignTemplateVariable($key, $value) {
		if ($this->templateVariableContainer->exists($key)) {
			$this->templateVariableContainer->remove($key);
		}
		$this->templateVariableContainer->add($key, $value);
		return $this;
	}

	/**
	 * Returns the value of a template variable. Or NULL if the variable is not set.
	 *
	 * @param string $key The key of a view variable to retrieve
	 * @return mixed The value of the requested variable or NULL if the variable does not exist.
	 */
	public function getTemplateVariable($key) {
		if ($this->templateVariableContainer->exists($key)) {
			return $this->templateVariableContainer->get($key);
		}
		return NULL;
	}

	/**
	 * Returns the title for the currently processed column.
	 * If a header gets rendered the ViewHelper argument "columnTitle" could have been set to some special label.
	 * If this is not the case the default language label for columns will get used.
	 *
	 * @return string The title for the current column based on the "columnTitle" argument passed to the ViewHelper
	 */
	protected function getColumnTitle() {
		$columnTitle = $this->columnTitle ? : 'LLL:EXT:kb_nescefe/Resources/Private/Language/locallang.xlf:column';
		if (substr($columnTitle, 0, 4) === 'LLL:') {
			$columnTitle = $GLOBALS['LANG']->sL($columnTitle);
		}
		$columnTitle = str_replace('###IDX###', $this->columnIndex + 1, $columnTitle);
		return $columnTitle;
	}


	/**
	 * Stores the current content element position in a template variable.
	 * This will get used to determine all available content areas for selection in the "parentPosition" select box.
	 *
	 * @return void
	 */
	protected function storeCurrentPosition() {
		if (($availableContentAreas = $this->getTemplateVariable('availableContentAreas')) instanceof \TYPO3\CMS\Fluid\Core\ViewHelper\TemplateVariableContainer) {
			$key = $this->elementPosition;
			$current = array();
			if ($availableContentAreas->exists($key)) {
				$current = $availableContentAreas->get($key);
				$availableContentAreas->remove($key);
			}
			if ($this->columnTitle) {
				$current[0] = $this->getColumnTitle();
			}
			if (!$current[0]) {
				$current[0] = $this->getColumnTitle();
			}
			$current[1] = $this->elementPosition;
			$availableContentAreas->add($key, $current);
		}
	}

	/*
	 * Renders the content elements inside a kb_nescefe container column / content area
	 *
	 * @return ThinkopenAt\KbNescefe\Slots\PageLayoutView The slot instance (singleton)
	 */
	public function renderViaHook() {
		$elementUids = array();
		foreach ($this->getContentElements() as $contentElement) {
			$elementUids[] = $contentElement->getUid();
		}

		$element = $this->context->getRenderedElement();
		$this->context->setElementPosition($this->elementPosition);

		$pageLayoutView = $this->context->getPageLayoutView();
		$localPageLayoutView = clone($pageLayoutView);
		$localPageLayoutView->tt_contentConfig['single'] = FALSE;
		$localPageLayoutView->tt_contentConfig['languageMode'] = FALSE;
		$localPageLayoutView->tt_contentConfig['sys_language_uid'] = $element->getSysLanguageUid();
		$localPageLayoutView->tt_contentConfig['cols'] = $this->containerElementColPos;
	
		$hookInstance = GeneralUtility::makeInstance('ThinkopenAt\KbNescefe\Hooks\PageLayoutView');
		$slotInstance = GeneralUtility::makeInstance('ThinkopenAt\KbNescefe\Slots\PageLayoutView');

		// @todo: Check if content elements have already been rendered
		/*
		if ($slotInstance->currentlyRenderedElements($elementUids)) {
			// Maybe those content elements have previously already been rendered
			return $slotInstance->getRenderedContent();
		}
		*/

		$slotInstance->reset();
		$hookInstance->setContentElementUids($elementUids);
		$localPageLayoutView->getTable_tt_content($element->getPid());
		$hookInstance->unsetContentElementUids();

		return $slotInstance;
	}

}

