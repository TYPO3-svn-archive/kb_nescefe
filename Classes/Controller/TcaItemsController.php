<?php
namespace ThinkopenAt\KbNescefe\Controller;

/***************************************************************
*  Copyright notice
*
*  (c) 2006-2015 Bernhard Kraft (kraftb@think-open.at)
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
 * Item processing class
 *
 * @author	Bernhard Kraft <kraftb@think-open.at>
 */

use \TYPO3\CMS\Core\Utility\MathUtility;
use \TYPO3\CMS\Backend\Utility\BackendUtility;

class TcaItemsController extends AbstractBackendController {

	/**
	 * @var boolean When rendering the TCA items retrieve the layout from this elements parent
	 */
	protected $getLayoutFromParent = TRUE;

	/**
	 * Entry method for generating content positions
	 *
	 * @param array $params: The variables passed to the hook
	 * @param \TYPO3\CMS\Backend\Form\FormEngine $parentObject: The object from which this user function is called
	 * @return array The processed items
	 */
	public function contentPositions(array $params, \TYPO3\CMS\Backend\Form\FormEngine $parentObject) {
		$this->baseControllerSetup();
		$this->processRequest($params, $parentObject);
		$this->baseControllerShutdown();
	}

	/**
	 * This method alters the available items/option in the drop down for selecting the position of a tt_content.
	 * element in its kb_nescefe parent.
	 * This method generates the FormEngine select items array (array<label:value>) from possible locations
	 * (paths) of this element in it's parent.
	 *
	 * @param array $params: The variables passed to the hook
	 * @param \TYPO3\CMS\Backend\Form\FormEngine $parentObject: The object from which this user function is called
	 * @return array The processed items
	 */
	protected function processRequest(array $params, \TYPO3\CMS\Backend\Form\FormEngine $parentObject) {
		$render = $this->checkPrerequisites($params, $parentObject);
		if (!$render) {
			$label = $GLOBALS['LANG']->sL('LLL:EXT:kb_nescefe/Resources/Private/Language/locallang.xlf:no_layout');
			$params['items'][] = array($label, $params['row']['kbnescefe_parentPosition']);
			return $params['items'];
		}

		$storeRenderedElement = $this->context->getRenderedElement();
		$this->context->setRenderedElement($this->element);

		$this->view->assign('element', $this->element);
		$this->view->assign('disableRealRendering', TRUE);
		$this->view->assign('availableContentAreas', $this->objectManager->get('TYPO3\CMS\Fluid\Core\ViewHelper\TemplateVariableContainer'));

		$this->view->render();

		$this->context->setRenderedElement($storeRenderedElement);

		$availableContentAreas = $this->view->getTemplateVariable('availableContentAreas');
		$params['items'] = array_values($availableContentAreas->getAll());
	}

	/*
	 * Checks whether the processing should take place.
	 * Checks various conditions/prerequisites which are required for proper rendering.
	 *
	 * @param array $params: Contains some parameters passed to this method by the page module
	 * @param \TYPO3\CMS\Backend\Form\FormEngine $parentObject: The object from which this user function is called
	 * @return boolean Returns TRUE if prerequisites are fullfilled and rendering should take place.
	 */
	protected function checkPrerequisites(array $params, \TYPO3\CMS\Backend\Form\FormEngine $parentObject) {
		if (!($parentObject instanceof \TYPO3\CMS\Backend\Form\FormEngine)) {
			return FALSE;
		}

		if ($params['table'] !== 'tt_content') {
			return FALSE;
		}
		$this->context->setFormHandler($parentObject);
		$this->context->setCurrentPage($params['row']['pid']);

		if (!$this->initializeAction($params['row'])) {
			return FALSE;
		}

		return TRUE;
	}


}

