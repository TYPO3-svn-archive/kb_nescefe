<?php
namespace ThinkopenAt\KbNescefe\Controller;

/***************************************************************
*  Copyright notice
*
*  (c) 2006-2015 Bernhard Kraft <kraftb@think-open.at>
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
 * Frontend controller
 *
 * @author	Bernhard Kraft <kraftb@think-open.at>
 */

use \TYPO3\CMS\Core\Utility\MathUtility;
use \TYPO3\CMS\Backend\Utility\BackendUtility;
use \TYPO3\CMS\Extbase\Mvc\View\ViewInterface;

class FrontendController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

	/**
	 * @var \ThinkopenAt\KbNescefe\Context\Frontend
	 * @inject
	 */
	protected $context;

	/**
	 * @var \ThinkopenAt\KbNescefe\Domain\Repository\ContentRepository
	 * @inject
	 */
	protected $contentRepository;

	/**
	 * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
	 * @inject
	 */
	protected $configurationManager;

	/**
	 * @var \ThinkopenAt\KbNescefe\Domain\Model\Content Currently rendered content element
	 */
	protected $element = NULL;

	/**
	 * @var \ThinkopenAt\KbNescefe\Domain\Model\Layout
	 */
	protected $layout = NULL;

	/**
	 * Initializes the controller before invoking an action method.
	 *
	 * Retrieves the currently processed content element and the associated layout.
	 *
	 * @return void
	 * @api
	 */
	protected function initializeAction() {
		$contentElement = $this->configurationManager->getContentObject()->data;
		if (!MathUtility::canBeInterpretedAsInteger($contentElement['uid'])) {
			$this->initializationFailed = 'no_element';
			return;
		}
		$this->contentRepository->setContext($this->context);

		$this->element = $this->contentRepository->findByIdentifier($contentElement['uid']);
		if (! $this->element instanceof \ThinkopenAt\KbNescefe\Domain\Model\Content) {
			$this->initializationFailed = 'no_element';
			return;
		}
		$this->context->setRenderedElement($this->element);

		$this->layout = $this->element->getLayout();
		if ( ! (
			$this->layout instanceof \ThinkopenAt\KbNescefe\Domain\Model\Layout ||
			$this->layout instanceof \TYPO3\CMS\Extbase\Persistence\Generic\LoadingStrategyInterface
		) ) {
			$this->initializationFailed = 'no_layout';
			return;
		}

		$this->defaultViewObjectName = 'ThinkopenAt\KbNescefe\View\Frontend';
	}

	/**
	 * Initializes the view before invoking an action method.
	 *
	 * This initializes the frontend view class. The view is implemented by ThinkopenAt\KbNescefe\Template\Frontend which
	 * extends the default view object "TYPO3\CMS\Fluid\View\TemplateView".
	 *
	 * @param ViewInterface $view The view to be initialized
	 *
	 * @return void
	 * @api
	 */
	protected function initializeView(ViewInterface $view) {
		try {
			$view->setLayout($this->layout);
		} catch (\ThinkopenAt\KbNescefe\Exceptions\InvalidSettingsException $e) {
			$this->initializationFailed = 'no_template_file';
		}
	}
	
	public function renderAction() {
		if ($this->initializationFailed) {
			$this->setErrorMessage();
			return;
		}

		$this->view->assign('element', $this->element);
	}

	protected function setErrorMessage() {
		$this->view->assign('error', $this->initializationFailed);
	}

}

