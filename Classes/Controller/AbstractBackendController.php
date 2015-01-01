<?php
namespace ThinkopenAt\KbNescefe\Controller;

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
 * Base controller
 *
 * @author	Bernhard Kraft <kraftb@think-open.at>
 */

use \TYPO3\CMS\Core\Utility\MathUtility;
use \TYPO3\CMS\Backend\Utility\BackendUtility;

abstract class AbstractBackendController {

	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManager
	 * @inject
	 */
	protected $objectManager;

	/**
	 * @var \TYPO3\CMS\Extbase\Object\Container\Container
	 * @inject
	 */
	protected $objectContainer;

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
	 * @var \ThinkopenAt\KbNescefe\View\Backend
	 * @inject
	 */
	protected $view = NULL;

	/**
	 * @var \ThinkopenAt\KbNescefe\Domain\Model\Content Currently rendered content element
	 */
	protected $element = NULL;

	/**
	 * @var \ThinkopenAt\KbNescefe\Domain\Model\Content Parent of currently rendered content element
	 */
	protected $parent = NULL;

	/**
	 * @var \ThinkopenAt\KbNescefe\Domain\Model\Layout
	 */
	protected $layout = NULL;

	/**
	 * @var \ThinkopenAt\KbNescefe\Template\Backend
	 */
	protected $template = NULL;

	/**
	 * @var string Will get set to an error descriptor if an error occured.
	 */
	protected $error = '';
	
	/**
	 * @var boolean When set to TRUE the layout will get retrieved from the parent record.
	 */
	protected $getLayoutFromParent = FALSE;

	/**
	 * @var array These are alternate classes which will just be set while this controller is active. Upon finishing the old implementation settings will get restored.
	 */
	protected $alternateClasses = array(
		'TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface' => array(
			'alternateClassName' => 'ThinkopenAt\KbNescefe\AlternateImplementations\QuerySettings',
		),
		'ThinkopenAt\KbNescefe\AlternateImplementations\Typo3QuerySettings' => array(
			'alternateClassName' => 'ThinkopenAt\KbNescefe\AlternateImplementations\QuerySettings',
		),
	);
	
	/**
	 * Method which gets called before hook processing starts
	 *
	 * @return void
	 */
	public function baseControllerSetup() {
		foreach ($this->alternateClasses as $baseClassName => $alternateInfo) {
//			This works in 7.0 as "getImplementationClassName" is public there:
//			$this->alternateClasses[$baseClassName]['backupAlternateClassName'] = $this->objectContainer->getImplementationClassName($baseClassName);
//			$this->objectContainer->registerImplementation($baseClassName, $alternateInfo['alternateClassName']);

			// For 6.2:
			$this->alternateClasses[$baseClassName]['backupAlternateClassName'] = $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][$baseClassName]['className'];
			$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][$baseClassName]['className'] = $alternateInfo['alternateClassName'];
		}
	}

	/**
	 * Method which gets called before hook processing starts
	 *
	 * @return void
	 */
	public function baseControllerShutdown() {
		foreach ($this->alternateClasses as $baseClassName => $alternateInfo) {
//			For 7.0:
//			$this->objectContainer->registerImplementation($baseClassName, $alternateInfo['backupAlternateClassName']);

//			For 6.2:
			$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][$baseClassName]['className'] = $alternateInfo['backupAlternateClassName'];
		}
	}

	/**
	 * This method initializes the required object instances.
	 *
	 * @param array $contentElement: The content element which is just getting rendered
	 * @return boolean If an error occured FALSE will get returned and the error message will get set in $this->error
	 */
	protected function initializeAction(array $contentElement) {

		$this->context->setLanguage($contentElement['sys_language_uid']);
		$this->contentRepository->setContext($this->context);

		if (!MathUtility::canBeInterpretedAsInteger($contentElement['uid'])) {
			if (substr($contentElement['uid'], 0, 3) === 'NEW') {
				if (preg_match('/^tt_content_([0-9]+)\|/', $contentElement['kbnescefe_parentElement'], $matches)) {
					$contentElement['kbnescefe_parentElement'] = $matches[1];
				}
				$dataMapper = $this->objectManager->get('TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper');
				list($this->element) = $dataMapper->map('\ThinkopenAt\KbNescefe\Domain\Model\Content', array($contentElement));
			} else {
				$this->error = 'invalid_element';
				return FALSE;
			}
		} else {
			$this->element = $this->contentRepository->findByIdentifier($contentElement['uid']);
		}

		if (! $this->element instanceof \ThinkopenAt\KbNescefe\Domain\Model\Content) {
			$this->error = 'invalid_element';
			return FALSE;
		}

		if ($this->getLayoutFromParent) {
			$this->parent = $this->element->getParentElement();
			if ( ! (
				$this->parent instanceof \ThinkopenAt\KbNescefe\Domain\Model\Content ||
				$this->parent instanceof \TYPO3\CMS\Extbase\Persistence\Generic\LoadingStrategyInterface
			) ) {
				$this->error = 'no_layout';
				return FALSE;
			}
			$this->layout = $this->parent->getLayout();
		} else {
			$this->layout = $this->element->getLayout();
		}

		if ( ! (
			$this->layout instanceof \ThinkopenAt\KbNescefe\Domain\Model\Layout ||
			$this->layout instanceof \TYPO3\CMS\Extbase\Persistence\Generic\LoadingStrategyInterface
		) ) {
			$this->error = 'no_layout';
			return FALSE;
		}

		try {
			$this->view->setLayout($this->layout);
		} catch (\ThinkopenAt\KbNescefe\Exceptions\InvalidSettingsException $e) {
			$this->error = 'invalid_template';
			return FALSE;
		}

		return TRUE;
	}

}

