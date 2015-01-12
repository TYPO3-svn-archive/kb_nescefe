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
 * Content preview hook
 *
 * @author	Bernhard Kraft <kraftb@think-open.at>
 */

use \TYPO3\CMS\Core\Utility\MathUtility;
use \TYPO3\CMS\Backend\Utility\BackendUtility;

class ContentPreviewController extends AbstractBackendController {

	/**
	 * @var string The rendered preview content
	 */
	protected $content = '';

	/*
	 * Just calls the wrapped "wrappedRenderPluginPreview" method so the "baseControllerSetup" and "baseControllerShutdown"
	 * methods in the base class can get called.
	 *
	 * @param array $params: Contains some parameters passed to this method by the page module
	 * @param \TYPO3\CMS\Backend\View\PageLayoutView $parentObject: The parent class from which this hook is called
	 * @return string The rendered preview content
	 * @see: EXT:backend/Classes/View/PageLayoutView.php:PageLayoutView->tt_content_drawItem (search for "list_type_Info" / hook)
	 */
	public function renderPluginPreview($params, $parentObject) {
		$this->content = '';
		$this->baseControllerSetup();
		$this->processRequest($params, $parentObject);
		$this->baseControllerShutdown();
		return $this->content;
	}

	/*
	 * Renders the content preview for a kb_nescefe plugin: It will show the columns of the container and its contents
	 * This is the hook method called from within the page module (PageLayoutView)
	 *
	 * @param array $params: Contains some parameters passed to this method by the page module
	 * @param \TYPO3\CMS\Backend\View\PageLayoutView $parentObject: The parent class from which this hook is called
	 * @return string The rendered preview content
	 * @see: EXT:backend/Classes/View/PageLayoutView.php:PageLayoutView->tt_content_drawItem (search for "list_type_Info" / hook)
	 */
	public function processRequest($params, $parentObject) {
		$render = $this->checkPrerequisites($params, $parentObject);
		if (!$render) {
			return;
		}

		$storeRenderedElement = $this->context->getRenderedElement();
		$this->context->setRenderedElement($this->element);

		$this->view->assign('element', $this->element);

		$this->content .= $this->view->render();

		$this->context->setRenderedElement($storeRenderedElement);
	}

	/*
	 * Checks whether the processing should take place.
	 * Checks various conditions/prerequisites which are required for proper rendering.
	 *
	 * @param array $params: Contains some parameters passed to this method by the page module
	 * @param \TYPO3\CMS\Backend\View\PageLayoutView $parentObject: The parent class from which this hook is called
	 * @return boolean Returns TRUE if prerequisites are fullfilled and rendering should take place.
	 */
	protected function checkPrerequisites($params, $parentObject) {
		global $LANG;
		if (!($parentObject instanceof \TYPO3\CMS\Backend\View\PageLayoutView)) {
			$this->content .= 'The extension "kb_nescefe" is only compatible with the default TYPO3 page module. If you use TemplaVoila then use Flexible Content Elements instead';
			return FALSE;
		}

		if ($parentObject->table !== 'tt_content') {
			return FALSE;
		}

		if ($params['row']['CType'] !== 'list' || $params['row']['list_type'] !== 'kbnescefe_pi1') {
			return FALSE;
		}

		if (!$params['row']['kbnescefe_layout'])	{
			$this->content .= '<strong>'.$LANG->sL('LLL:EXT:kb_nescefe/Resources/Private/Language/locallang.xlf:select_layout').'</strong>';
			return FALSE;
		}

		$this->context->setPageLayoutView($parentObject);

		if (!$this->initializeAction($params['row'])) {
			$this->content .= $GLOBALS['LANG']->sL('LLL:EXT:kb_nescefe/Resources/Private/Language/locallang.xlf:layout_invalid');
			return FALSE;
		}

		return TRUE;
	}

}

